<?php
/**
 * Image, shows, creates, resizes and saves images to cash.
 *
 */
class Image
{
    private $maxWidth;
    private $maxHeight;
    private $directory;
    private $src;
    private $imagePath;
    private $cachePath;
    private $verbose;
    private $saveAs;
    private $quality;
    private $ignoreCache;
    private $newWidth;
    private $newHeight;
    private $cropToFit;
    private $sharpen;
    private $pathToImage;
    private $filterType;

    /**
     * Constructor
     *
     * Initiates the member parameters.
     *
     * @param string  $directory the directory for the calling page.
     * @param string  $src the image source.
     * @param integer $maxWidth  the maximum allowed width for a picture, default is 2000px.
     * @param integer $maxHeight the maximum allowed height for a picture, default is 2000px.
     */
    public function __construct($directory, $src, $maxWidth=2000, $maxHeight=2000)
    {
        $this->dirctory = $directory;
        $this->src = $src;
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        $this->imagePath = $directory . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR;
        $this->cachePath = $directory . '/cache/';
        $this->pathToImage = realpath($this->imagePath . $this->src);

    }

    /**
     * Process image.
     *
     * Processes the image according to the values in the parameter array. The image
     * can be resized, be stored as JPEG or PNG and it is possible to applay filters
     * such as be sharpened. The image is also stored in a cach to decrease the time
     * when the image is presented.
     *
     * After the image has been processed, the image is presented.
     *
     * @param  [] $params information how to process the image.
     *
     * @return void.
     */
    public function processAndOutputImage($params)
    {
        $this->saveParameters($params);
        $this->validateParameters();

        if ($this->verbose) {
            $query = array();
            parse_str($_SERVER['QUERY_STRING'], $query);
            unset($query['verbose']);
            $url = '?' . http_build_query($query);

          echo <<<EOD
            <html lang='en'>
            <meta charset='UTF-8'/>
            <title>img.php verbose mode</title>
            <h1>Verbose mode</h1>
            <p><a href=$url><code>$url</code></a><br>
            <img src='{$url}' /></p>
EOD;
        }

        // Get information on the image
        $imgInfo = list($width, $height, $type, $attr) = getimagesize($this->pathToImage);
        !empty($imgInfo) or $this->errorMessage("The file doesn't seem to be an image.");
        $mime = $imgInfo['mime'];

        if ($this->verbose) {
            $filesize = filesize($this->pathToImage);
            $this->verbose("Image file: {$this->pathToImage}");
            $this->verbose("Image information: " . print_r($imgInfo, true));
            $this->verbose("Image width x height (type): {$width} x {$height} ({$type}).");
            $this->verbose("Image file size: {$filesize} bytes.");
            $this->verbose("Image mime type: {$mime}.");
        }

        // Calculate new width and height for the image
        $aspectRatio = $width / $height;

        if ($this->cropToFit && $this->newWidth && $this->newHeight) {
            $targetRatio = $this->newWidth / $this->newHeight;
            $cropWidth   = $targetRatio > $aspectRatio ? $width : round($height * $targetRatio);
            $cropHeight  = $targetRatio > $aspectRatio ? round($width  / $targetRatio) : $height;
            if ($this->verbose) {
                $this->verbose("Crop to fit into box of {$this->newWidth}x{$this->newHeight}. Cropping dimensions: {$cropWidth}x{$cropHeight}.");
            }
        }
        else if ($this->newWidth && !$this->newHeight) {
            $this->newHeight = round($this->newWidth / $aspectRatio);
            if ($this->verbose) {
                $this->verbose("New width is known {$this->newWidth}, height is calculated to {$this->newHeight}.");
            }
        }
        else if (!$this->newWidth && $this->newHeight) {
            $this->newWidth = round($this->newHeight * $aspectRatio);
            if ($this->verbose) {
                $this->verbose("New height is known {$this->newHeight}, width is calculated to {$this->newWidth}.");
            }
        }
        else if ($this->newWidth && $this->newHeight) {
            $ratioWidth  = $width  / $this->newWidth;
            $ratioHeight = $height / $this->newHeight;
            $ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
            $this->newWidth  = round($width  / $ratio);
            $this->newHeight = round($height / $ratio);
            if ($this->verbose) {
                $this->verbose("New width & height is requested, keeping aspect ratio results in {$this->newWidth}x{$this->newHeight}.");
            }
        }
        else {
            $this->newWidth = $width;
            $this->newHeight = $height;
            if ($this->verbose) {
                $this->verbose("Keeping original width & heigth.");
            }
        }

        // Creating a filename for the cache
        $parts          = pathinfo($this->pathToImage);
        $fileExtension  = $parts['extension'];
        $this->saveAs   = is_null($this->saveAs) ? $fileExtension : $this->saveAs;
        $quality_       = is_null($this->quality) ? null : "_q{$this->quality}";
        $cropToFit_     = is_null($this->cropToFit) ? null : "_cf";
        $sharpen_       = is_null($this->sharpen) ? null : "_s";
        $filterType_    = is_null($this->filterType) ? null : "_ft{$this->filterType}";
        $dirName        = preg_replace('/\//', '-', dirname($this->src));
        $cacheFileName = $this->cachePath . "-{$dirName}-{$parts['filename']}_{$this->newWidth}_{$this->newHeight}{$quality_}{$cropToFit_}{$sharpen_}{$filterType_}.{$this->saveAs}";
        $cacheFileName = preg_replace('/^a-zA-Z0-9\.-_/', '', $cacheFileName);

        if ($this->verbose) {
            $this->verbose("Cache file is: {$cacheFileName}");
        }

        // Is there already a valid image in the cache directory, then use it and exit
        $imageModifiedTime = filemtime($this->pathToImage);
        $cacheModifiedTime = is_file($cacheFileName) ? filemtime($cacheFileName) : null;

        // If cached image is valid, output it.
        if (!$this->ignoreCache && is_file($cacheFileName) && $imageModifiedTime < $cacheModifiedTime) {
          if ($this->verbose) {
              $this->verbose("Cache file is valid, output it.");
          }
          $this->outputImage($cacheFileName);
        }

        if ($this->verbose) {
            $this->verbose("Cache is not valid, process image and create a cached version of it.");
        }

        // Open up the original image from file
        if ($this->verbose) {
            $this->verbose("File extension is: {$fileExtension}");
        }

        $image = $this->createImage($fileExtension);

        // Resize the image if needed
        if ($this->cropToFit) {
            if($this->verbose) {
                $this->verbose("Resizing, crop to fit.");
            }

            $cropX = round(($width - $cropWidth) / 2);
            $cropY = round(($height - $cropHeight) / 2);
            $imageResized = $this->createImageKeepTransparency($this->newWidth, $this->newHeight);
            imagecopyresampled($imageResized, $image, 0, 0, $cropX, $cropY, $this->newWidth, $this->newHeight, $cropWidth, $cropHeight);
            $image = $imageResized;
            $width = $this->newWidth;
            $height = $this->newHeight;
        }
        else if (!($this->newWidth == $width && $this->newHeight == $height)) {
            if($this->verbose) {
                $this->verbose("Resizing, new height and/or width.");
            }

            $imageResized = $this->createImageKeepTransparency($this->newWidth, $this->newHeight);
            imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $width, $height);
            $image  = $imageResized;
            $width  = $this->newWidth;
            $height = $this->newHeight;
        }

        // Apply filters and postprocessing of image
        if ($this->filterType) {
            $image = $this->addFilter($image);
        }

        if ($this->sharpen) {
            $image = $this->sharpenImage($image);
        }

        $this->saveImage($image, $cacheFileName);

        if ($this->verbose) {
            clearstatcache();
            $cacheFilesize = filesize($cacheFileName);
            $this->verbose("File size of cached file: {$cacheFilesize} bytes.");
            $this->verbose("Cache file has a file size of " . round($cacheFilesize/$filesize*100) . "% of the original size.");
        }

        // Output the resulting image
        $this->outputImage($cacheFileName);
    }

    /**
     * Save parameters
     *
     * Saves the processing parameters as member parameters.
     *
     * @param  [] $params the image processing parameters.
     *
     * @return void.
     */
    private function saveParameters($params)
    {
        $this->verbose = $params['verbose'];
		$this->saveAs = $params['saveAs'];
		$this->quality = $params['quality'];
		$this->ignoreCache = $params['ignoreCache'];
		$this->newWidth = $params['newWidth'];
		$this->newHeight = $params['newHeight'];
		$this->cropToFit = $params['cropToFit'];
		$this->sharpen = $params['sharpen'];
        $this->filterType = $params['filterType'];
    }

    /**
     * Validate parameters.
     *
     * Validates imgage processing parameters.
     *
     * @return void.
     */
    private function validateParameters()
    {
        is_dir($this->imagePath) or $this->errorMessage('The image dir is not a valid directory.');
        is_writable($this->cachePath) or $this->errorMessage('The cache dir is not a writable directory.');
        isset($this->src) or $this->errorMessage('Must set src-attribute.');
        preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $this->src) or $this->errorMessage('Filename contains invalid characters.');
        substr_compare($this->imagePath, $this->pathToImage, 0, strlen($this->imagePath)) == 0 or $this->errorMessage('Security constraint: Source image is not directly below the directory IMG_PATH.');
        is_null($this->saveAs) or in_array($this->saveAs, array('png', 'jpg', 'jpeg', 'gif')) or $this->errorMessage('Not a valid extension to save image as');
        is_null($this->quality) or (is_numeric($this->quality) and $this->quality > 0 and $this->quality <= 100) or $this->errorMessage('Quality out of range');
        is_null($this->newWidth) or (is_numeric($this->newWidth) and $this->newWidth > 0 and $this->newWidth <= $this->maxWidth) or $this->errorMessage('Width out of range');
        is_null($this->newHeight) or (is_numeric($this->newHeight) and $this->newHeight > 0 and $this->newHeight <= $this->maxHeight) or $this->errorMessage('Height out of range');
        is_null($this->cropToFit) or ($this->cropToFit and $this->newWidth and $this->newHeight) or $this->errorMessage('Crop to fit needs both width and height to work');
        is_null($this->filterType) or in_array($this->filterType, array('grayscale', 'sepia')) or $this->errorMessage('Not a valid image filter');
    }

    /**
     * Display error message.
     *
     * @param  string $message the error message to display.
     *
     * @return void
     */
    function errorMessage($message)
    {
        header("Status: 404 Not Found");
        die('img.php says 404 - ' . htmlentities($message));
    }

    /**
     * Display log message.
     *
     * @param  string $message the log message to display.
     *
     * @return void
     */
    private function verbose($message)
    {
		echo "<p>" . htmlentities($message) . "</p>";
	}

    /**
     * Output an image together with last modified header.
     *
     * @param string $file as path to the image.
     *
     * @param boolean $verbose if verbose mode is on or off.
     */
    private function outputImage($file)
    {
        $info = getimagesize($file);
        !empty($info) or $this->errorMessage("The file doesn't seem to be an image.");
        $mime   = $info['mime'];

        $lastModified = filemtime($file);
        $gmdate = gmdate("D, d M Y H:i:s", $lastModified);

        if ($this->verbose) {
            $this->verbose("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
            $this->verbose("Memory limit: " . ini_get('memory_limit'));
            $this->verbose("Time is {$gmdate} GMT.");
        }

        if (!$this->verbose)
            header('Last-Modified: ' . $gmdate . ' GMT');

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified) {
            if ($this->verbose) {
                $this->verbose("Would send header 304 Not Modified, but its verbose mode.");
                exit;
            }
            header('HTTP/1.0 304 Not Modified');
        } else {
            if ($this->verbose) {
                $this->verbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode.");
                exit;
            }
            header('Content-type: ' . $mime);
            readfile($file);
        }
        exit;
    }

    /**
     * Create Image.
     *
     * Creates an image according to the file extension.
     *
     * @param  string $fileExtension the extension of the file.
     *
     * @return object the image source identifier.
     */
    private function createImage($fileExtension)
    {
        $image = Null;
        switch($fileExtension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($this->pathToImage);
                if($this->verbose) {
                    $this->verbose("Opened the image as a JPEG image.");
                }
                break;

            case 'png':
                $image = imagecreatefrompng($this->pathToImage);
                if ($this->verbose) {
                    $this->verbose("Opened the image as a PNG image.");
                }
                break;

            case 'gif':
                $image = imagecreatefromgif($this->pathToImage);
                if ($this->verbose) {
                    $this->verbose("Opened the image as a GIF image.");
                }
                break;

            default:
                $this->errorMessage('No support for this file extension.');
        }

        return $image;
    }

    /**
     * Create new image and keep transparency
     *
     * @param resource $image the image to apply this filter on.
     *
     * @return resource $image as the processed image.
     */
    private function createImageKeepTransparency($width, $height)
    {
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        return $img;
    }

    /**
     * Add filter
     *
     * Adds a filter type to the image. Supported filter types is grayscale and
     * sepia.
     *
     * @param resource $image the image to apply this filter on.
     *
     * @return resource $image as the processed image.
     */
    private function addFilter($image)
    {
        switch($this->filterType) {
            case 'grayscale':
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                if($this->verbose) {
                    $this->verbose("Added grayscale filter to image.");
                }
                break;

            case 'sepia':
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                imagefilter($image, IMG_FILTER_COLORIZE, 112, 66, 20, 60);
                if ($this->verbose) {
                    $this->verbose("Added sepia filter to image.");
                }
                break;

            default:
                $this->errorMessage('No support for this filter type.');
        }

        return $image;
    }

    /**
     * Sharpen image as http://php.net/manual/en/ref.image.php#56144
     * http://loriweb.pair.com/8udf-sharpen.html
     *
     * @param resource $image the image to apply this filter on.
     *
     * @return resource $image as the processed image.
     */
    private function sharpenImage($image)
    {
        $matrix = array(
            array(-1,-1,-1,),
            array(-1,16,-1,),
            array(-1,-1,-1,)
        );

        $divisor = 8;
        $offset = 0;
        imageconvolution($image, $matrix, $divisor, $offset);

        return $image;
    }

    /**
     * Save image.
     *
     * Outputs an image to a file.
     *
     * @param  image resource $image the image resource.
     * @param  string $cacheFileName the path to save the file to.
     *
     * @return void.
     */
    private function saveImage($image, $cacheFileName)
    {
        switch($this->saveAs) {
            case 'jpeg':
            case 'jpg':
                if ($this->verbose) {
                    $this->verbose("Saving image as JPEG to cache using quality = {$this->quality}.");
                }
                imagejpeg($image, $cacheFileName, $this->quality);
                break;

            case 'png':
                if ($this->verbose) {
                    $this->verbose("Saving image as PNG to cache.");
                }
                imagepng($image, $cacheFileName);
                break;

            case 'gif':
                if ($this->verbose) {
                    $this->verbose("Saving image as GIF to cache.");
                }
                imagegif($image, $cacheFileName);
                break;

            default:
                $this->errorMessage('No support to save as this file extension.');
                break;
        }
    }

}
