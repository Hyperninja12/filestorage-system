<?php

namespace App\Http\Controllers;

use App\Models\FileFolder;
use App\Models\InventoryFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileInventoryController extends Controller
{
    /**
     * Show root level folders and files.
     */
    public function index(Request $request)
    {
        return $this->renderFolderView($request, null);
    }

    /**
     * Show contents of a specific folder.
     */
    public function showFolder(Request $request, FileFolder $folder)
    {
        return $this->renderFolderView($request, $folder);
    }

    /**
     * Shared logic for rendering the inventory view (root or inside a folder).
     */
    private function renderFolderView(Request $request, ?FileFolder $currentFolder)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'name_asc');

        // Folders Query
        $foldersQuery = FileFolder::query();
        
        // Files Query
        $filesQuery = InventoryFile::query();

        if ($search) {
            // Global search: ignore current folder, find anywhere
            $foldersQuery->where('name', 'like', "%{$search}%");
            $filesQuery->where('original_name', 'like', "%{$search}%")
                       ->orWhere('description', 'like', "%{$search}%");
            $currentFolder = null; // Reset breadcrumbs for global search
        } else {
            // Scope to current folder
            $folderId = $currentFolder ? $currentFolder->id : null;
            $foldersQuery->where('parent_id', $folderId);
            $filesQuery->where('folder_id', $folderId);
        }

        // Sorting
        switch ($sort) {
            case 'name_desc':
                $foldersQuery->orderBy('name', 'desc');
                $filesQuery->orderBy('original_name', 'desc');
                break;
            case 'date_asc':
                $foldersQuery->orderBy('created_at', 'asc');
                $filesQuery->orderBy('created_at', 'asc');
                break;
            case 'date_desc':
                $foldersQuery->orderBy('created_at', 'desc');
                $filesQuery->orderBy('created_at', 'desc');
                break;
            case 'size_asc':
                $foldersQuery->orderBy('name', 'asc'); // Folders don't have size
                $filesQuery->orderBy('file_size', 'asc');
                break;
            case 'size_desc':
                $foldersQuery->orderBy('name', 'asc');
                $filesQuery->orderBy('file_size', 'desc');
                break;
            case 'type_asc':
                $foldersQuery->orderBy('name', 'asc');
                $filesQuery->orderBy('file_extension', 'asc');
                break;
            case 'type_desc':
                $foldersQuery->orderBy('name', 'asc');
                $filesQuery->orderBy('file_extension', 'desc');
                break;
            case 'name_asc':
            default:
                $foldersQuery->orderBy('name', 'asc');
                $filesQuery->orderBy('original_name', 'asc');
                break;
        }

        $folders = $foldersQuery->get();
        $files = $filesQuery->get();

        // Build breadcrumbs
        $breadcrumbs = [];
        $tempFolder = $currentFolder;
        while ($tempFolder) {
            array_unshift($breadcrumbs, $tempFolder);
            $tempFolder = $tempFolder->parent;
        }

        // System stats
        $totalFiles = InventoryFile::count();
        $totalFolders = FileFolder::count();
        $totalSize = InventoryFile::sum('file_size');
        
        // Format total size
        $bytes = $totalSize;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        for (; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        $formattedTotalSize = round($bytes, 2) . ' ' . ($units[$i] ?? 'B');

        // Get all folders for the "Move File" modal dropdown
        $allFolders = FileFolder::orderBy('name', 'asc')->get();

        return view('file-inventory.index', compact(
            'currentFolder', 
            'folders', 
            'files', 
            'breadcrumbs', 
            'sort', 
            'search',
            'totalFiles',
            'totalFolders',
            'formattedTotalSize',
            'allFolders'
        ));
    }

    /**
     * Create a new folder.
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:file_folders,id',
            'color' => 'nullable|string|max:50',
        ]);

        FileFolder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'color' => $request->color,
        ]);

        return back()->with('success', 'Folder created successfully.');
    }

    /**
     * Rename a folder.
     */
    public function renameFolder(Request $request, FileFolder $folder)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
        ]);

        $folder->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return back()->with('success', 'Folder updated successfully.');
    }

    /**
     * Move a folder to another folder.
     */
    public function moveFolder(Request $request, FileFolder $folder)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:file_folders,id',
        ]);

        if ($request->parent_id == $folder->id) {
            return back()->with('error', 'Cannot move a folder into itself.');
        }

        $folder->update(['parent_id' => $request->parent_id]);

        return back()->with('success', 'Folder moved successfully.');
    }

    /**
     * Delete a folder and all its contents (handled by DB cascade on inventory_files 
     * but we need to delete the actual physical files).
     */
    public function deleteFolder(FileFolder $folder)
    {
        // Recursively find all nested files and delete their physical storage
        $this->deletePhysicalFilesInFolder($folder);
        
        $folder->delete();

        // If we were inside the folder, redirect to its parent, otherwise just back
        if (request()->headers->get('referer') && str_contains(request()->headers->get('referer'), '/folder/' . $folder->id)) {
            if ($folder->parent_id) {
                return redirect()->route('file-inventory.folder', $folder->parent_id)->with('success', 'Folder deleted.');
            }
            return redirect()->route('file-inventory.index')->with('success', 'Folder deleted.');
        }

        return back()->with('success', 'Folder deleted.');
    }

    /**
     * Helper to recursively delete physical files in a folder before DB deletion.
     */
    private function deletePhysicalFilesInFolder(FileFolder $folder)
    {
        // Delete files in this folder
        foreach ($folder->files as $file) {
            Storage::disk('public')->delete($file->storage_path);
        }

        // Recurse into children
        foreach ($folder->children as $child) {
            $this->deletePhysicalFilesInFolder($child);
        }
    }

    /**
     * Upload one or multiple files.
     */
    public function uploadFiles(Request $request)
    {
        $request->validate([
            'folder_id' => 'nullable|exists:file_folders,id',
            'files' => 'required|array',
            'files.*' => 'required|file|max:10240', // 10MB max per file
        ]);

        $folderId = $request->folder_id;
        $yearMonth = date('Y-m');

        foreach ($request->file('files') as $uploadedFile) {
            $originalName = $uploadedFile->getClientOriginalName();
            $extension = $uploadedFile->getClientOriginalExtension() ?: $uploadedFile->extension();
            
            // Ensure unique filename in storage to avoid collisions
            $filename = Str::uuid() . '.' . $extension;
            $path = $uploadedFile->storeAs("inventory/{$yearMonth}", $filename, 'public');

            InventoryFile::create([
                'folder_id' => $folderId,
                'original_name' => $originalName,
                'storage_path' => $path,
                'file_type' => $uploadedFile->getMimeType() ?? 'application/octet-stream',
                'file_extension' => strtolower($extension),
                'file_size' => $uploadedFile->getSize(),
            ]);
        }

        return back()->with('success', 'File(s) uploaded successfully.');
    }

    /**
     * Show a file details / view page before downloading.
     */
    public function showFile(InventoryFile $file)
    {
        $file->load('folder');

        // Get all folders for the "Move File" dropdown
        $allFolders = FileFolder::orderBy('name', 'asc')->get();

        return view('file-inventory.show', compact('file', 'allFolders'));
    }

    /**
     * Download a file.
     */
    public function downloadFile(InventoryFile $file)
    {
        $path = Storage::disk('public')->path($file->storage_path);
        
        if (!file_exists($path)) {
            abort(404, 'File not found on disk.');
        }

        return response()->download($path, $file->original_name);
    }

    /**
     * Preview a file inline (useful for images and PDFs).
     */
    public function previewFile(InventoryFile $file)
    {
        $path = Storage::disk('public')->path($file->storage_path);
        
        if (!file_exists($path)) {
            abort(404, 'File not found on disk.');
        }

        return response()->file($path);
    }

    /**
     * Rename a file.
     */
    public function renameFile(Request $request, InventoryFile $file)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $file->update([
            'original_name' => $request->name,
        ]);

        return back()->with('success', 'File renamed successfully.');
    }

    /**
     * Move a file to another folder.
     */
    public function moveFile(Request $request, InventoryFile $file)
    {
        $request->validate([
            'folder_id' => 'nullable|exists:file_folders,id',
        ]);

        $file->update(['folder_id' => $request->folder_id]);

        return back()->with('success', 'File moved successfully.');
    }

    /**
     * Delete a file.
     */
    public function deleteFile(InventoryFile $file)
    {
        $folderId = $file->folder_id;

        Storage::disk('public')->delete($file->storage_path);
        $file->delete();

        if ($folderId) {
            return redirect()->route('file-inventory.folder', $folderId)->with('success', 'File deleted successfully.');
        }

        return redirect()->route('file-inventory.index')->with('success', 'File deleted successfully.');
    }
}
