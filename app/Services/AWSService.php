<?php

namespace App\Services;

use Exception;
use Illuminate\Http\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use App\Enums\UploadFolderName;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class AWSService
{
    /**
     * Store a file in Amazon S3
     *
     * @param UploadFolderName $uploadFolderName
     * @param File|UploadedFile $file
     * @return string
     * @throws InvalidArgumentException
     */
    public static function store(UploadFolderName $uploadFolderName, $file)
    {
        if ($file instanceof UploadedFile) {

            $filePath = self::createTempFile($file);

            self::optimizeImage($filePath);

            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $s3Path = $uploadFolderName->value . '/' . $fileName;

            Storage::disk('s3')->put($s3Path, file_get_contents($filePath));

            unlink($filePath);

            return self::pathToUrl($s3Path);
        }

        if ($file instanceof HtmlString) {
            return self::storeHtmlString($uploadFolderName, $file);
        }

        throw new InvalidArgumentException('Invalid file type');
    }

    /**
     * Create a temporary file for processing
     *
     * @param UploadedFile $file
     * @return string
     * @throws Exception
     */
    private static function createTempFile(UploadedFile $file)
    {
        $tempDir = storage_path('app/temp/');

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $tempDir . $fileName;

        copy($file->getRealPath(), $filePath);

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("File could not be found or is not readable: $filePath");
        }

        return $filePath;
    }

    /**
     * Optimize an image file before uploading to S3
     *
     * @param string $filePath
     */
    private static function optimizeImage($filePath)
    {
        try {

            $optimizerChain = OptimizerChainFactory::create();
            $optimizerChain->optimize($filePath);

        } catch (Exception $e) {

            // Log the error but don't fail the upload
            Log::warning("Image optimization failed: " . $e->getMessage());

        }
    }

    /**
     * Store an HTML string as an image in S3
     *
     * @param UploadFolderName $uploadFolderName
     * @param HtmlString $file
     * @return string
     */
    private static function storeHtmlString(UploadFolderName $uploadFolderName, $file)
    {
        $fileName = Str::uuid() . '.png';
        $s3Path = $uploadFolderName->value . '/' . $fileName;

        Storage::disk('s3')->put($s3Path, (string) $file);

        return self::pathToUrl($s3Path);
    }

    /**
     * Delete the specified file in Amazon S3 using the specified URL
     *
     * @param string $url
     * @return bool
     */
    public static function delete($url)
    {
        if (self::exists($url)) {
            return Storage::disk('s3')->delete(self::urlToPath($url));
        }

        return true;
    }

    /**
     * Check if the specified file in Amazon S3 exists using the specified URL
     *
     * @param string $url
     * @return bool
     */
    public static function exists($url)
    {
        return Storage::disk('s3')->exists(self::urlToPath($url));
    }

    /**
     * Generate the Amazon file URL using the specified path
     *
     * @param string $path The path to the file e.g "logos/somelogo.png"
     * @return string
     * @throws Exception
     */
    public static function pathToUrl($path)
    {
        if (empty(config('filesystems.disks.s3.region'))) {
            throw new Exception('The AWS default region must be provided');
        } elseif (empty(config('filesystems.disks.s3.bucket'))) {
            throw new Exception('The AWS bucket must be provided');
        } elseif (empty($path)) {
            throw new Exception('The file path must be provided');
        }

        return 'https://s3.' . config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket') . '/' . $path;
    }

    /**
     * Generate the Amazon file path using the specified URL
     *
     * @param string $url The url to the file e.g "https://s3.eu-west-2.amazonaws.com/bonako/logos/somelogo.png"
     * @return string
     * @throws Exception
     */
    public static function urlToPath($url)
    {
        if (empty(config('filesystems.disks.s3.bucket'))) {
            throw new Exception('The AWS bucket must be provided');
        } elseif (empty($url)) {
            throw new Exception('The url must be provided');
        }

        return Arr::last(explode(config('filesystems.disks.s3.bucket') . '/', $url));
    }
}
