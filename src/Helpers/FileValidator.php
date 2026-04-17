<?php
/**
 * File/Folder Validation Helper
 * Centralized validation logic for file management
 */

namespace App\Helpers;

class FileValidator
{
    /**
     * Validate folder access permission
     * @return array [bool $hasAccess, string|null $error]
     */
    public static function canAccessFolder(?array $folder, ?int $userOrgId, bool $isAdmin): array
    {
        if (!$folder) {
            return [false, 'Folder not found'];
        }
        
        if ($isAdmin) {
            return [true, null];
        }
        
        $folderOrgId = $folder['organization_id'];
        
        // Allow Central folder (org_id = null) or own org
        if ($folderOrgId === null || $folderOrgId == $userOrgId) {
            return [true, null];
        }
        
        return [false, 'Access denied: You do not have permission to access this folder'];
    }
    
    /**
     * Validate upload permission
     * @return array [bool $canUpload, string|null $error]
     */
    public static function canUploadToFolder(?array $folder, ?int $userOrgId, bool $isAdmin): array
    {
        if (!$folder) {
            return [false, 'Folder not found'];
        }
        
        if ($isAdmin) {
            return [true, null];
        }
        
        $folderOrgId = $folder['organization_id'];
        
        // User can upload ONLY to their own org (NOT Central folder)
        if ($folderOrgId !== null && $folderOrgId == $userOrgId) {
            return [true, null];
        }
        
        if ($folderOrgId === null) {
            return [false, 'Cannot upload to Central folder (Admin only)'];
        }
        
        return [false, 'Access denied: can only upload to your organization folder'];
    }
    
    /**
     * Validate file type and size
     */
    public static function validateFileUpload(array $file, array $allowedTypes, int $maxSize): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Upload error: ' . $file['error'];
        }
        
        if ($file['size'] > $maxSize) {
            $maxMB = round($maxSize / 1048576, 1);
            return "File too large. Maximum size: {$maxMB}MB";
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            return "File type '{$ext}' is not allowed";
        }
        
        return null; // Valid
    }
    
    /**
     * Sanitize folder/file name
     */
    public static function sanitizeName(string $name): string
    {
        // Remove dangerous characters
        $name = str_replace(['/', '\\', '..', "\0"], '', $name);
        $name = trim($name);
        return $name;
    }
}
