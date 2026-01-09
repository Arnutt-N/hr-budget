<?php
/**
 * File Model
 * 
 * Handles file upload, storage, and retrieval
 */

namespace App\Models;

use App\Core\Database;

class File
{
    const UPLOAD_BASE = 'uploads';
    const ALLOWED_TYPES = ['pdf', 'xlsx', 'xls', 'csv', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'gif'];
    const MAX_SIZE = 10 * 1024 * 1024; // 10MB

    /**
     * Get files in a folder
     */
    public static function getByFolder(?int $folderId): array
    {
        $sql = "SELECT f.*, u.name as uploaded_by_name 
                FROM files f 
                LEFT JOIN users u ON f.uploaded_by = u.id 
                WHERE f.folder_id " . ($folderId ? "= ?" : "IS NULL") . "
                ORDER BY f.created_at DESC";
        
        return Database::query($sql, $folderId ? [$folderId] : []);
    }

    /**
     * Find file by ID
     */
    public static function find(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM files WHERE id = ?", [$id]);
    }

    /**
     * Upload a file
     */
    public static function upload(array $file, ?int $folderId, int $uploadedBy, ?string $description = null): int|string
    {
        // 1. Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Upload error: ' . $file['error'];
        }

        if ($file['size'] > self::MAX_SIZE) {
            return 'File too large. Max size: 10MB';
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_TYPES)) {
            return 'File type not allowed: ' . $ext;
        }

        // 2. Determine storage path
        $relativePath = self::UPLOAD_BASE;
        
        if ($folderId) {
            $folder = Folder::find($folderId);
            if ($folder && !empty($folder['folder_path'])) {
                // Use folder structure: uploads/2568/Category/SubFolder
                // Sanitize path to prevent traversal
                $cleanPath = str_replace('..', '', $folder['folder_path']);
                // Ensure correct directory separator
                $cleanPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $cleanPath);
                $relativePath .= DIRECTORY_SEPARATOR . $cleanPath;
            } else {
                // Fallback if folder not found
                $relativePath .= DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . 'orphaned';
            }
        } else {
            // Root upload (should generally be avoided in this structure)
            $relativePath .= DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . 'root';
        }

        // 3. Create directory
        $fullPath = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $relativePath;
        
        if (!is_dir($fullPath)) {
            if (!mkdir($fullPath, 0755, true)) {
                return 'Failed to create directory: ' . $relativePath;
            }
        }

        // 4. Generate unique filename and move file
        $storedName = uniqid() . '_' . time() . '.' . $ext;
        $destination = $fullPath . DIRECTORY_SEPARATOR . $storedName;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return 'Failed to move uploaded file';
        }

        // 5. Save to database
        // Store path with forward slashes for web consistency
        $webPath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

        return Database::insert('files', [
            'folder_id' => $folderId,
            'original_name' => $file['name'],
            'stored_name' => $storedName,
            'file_path' => $webPath . '/' . $storedName,
            'file_type' => $ext,
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
            'description' => $description,
            'uploaded_by' => $uploadedBy
        ]);
    }

    /**
     * Delete file (also removes from disk)
     */
    public static function delete(int $id): bool
    {
        $file = self::find($id);
        if (!$file) return false;

        // Delete from disk
        // Ensure path uses correct separator for OS
        $osPath = str_replace('/', DIRECTORY_SEPARATOR, $file['file_path']);
        $fullPath = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $osPath;
        
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Delete from database
        return Database::delete('files', 'id = ?', [$id]) > 0;
    }

    /**
     * Get file icon based on type
     */
    public static function getIcon(string $type): string
    {
        $icons = [
            'pdf' => 'file-text',
            'xlsx' => 'file-spreadsheet',
            'xls' => 'file-spreadsheet',
            'csv' => 'file-spreadsheet',
            'doc' => 'file-text',
            'docx' => 'file-text',
            'png' => 'image',
            'jpg' => 'image',
            'jpeg' => 'image',
            'gif' => 'image',
        ];
        return $icons[$type] ?? 'file';
    }

    /**
     * Format file size for display
     */
    public static function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
