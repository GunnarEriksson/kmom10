<?php
/**
 * Gallery, creates a gallery of images that are stored in folders.
 *
 */
class Gallery
{
    private $galleryPath;
    private $galleryBaseUrl;
    private $basePath;
    private $pathToGallery;

    /**
     * Constructor
     *
     * Initiates member parameters.
     *
     * @param string $galleryBaseUrl the gallery base url.
     * @param string $galleryPath path and name to the gallery root directory.
     * @param string $path path and name to the pointed (clicked) folder or image.
     */
    public function __construct($galleryBaseUrl, $galleryPath, $path)
    {
        $this->galleryBaseUrl = $galleryBaseUrl;
        $this->galleryPath = $galleryPath;
        $pathComplete = $galleryPath . DIRECTORY_SEPARATOR . $path;
        $this->pathToGallery = realpath($pathComplete);
        $this->basePath = realpath($galleryPath);
    }

    /**
     * Create gallery.
     *
     * If the gallery path is to a gallery (folders and items), the gallery is
     * presented as a list. If the gallery path is to an item, the item and
     * available information about the item is presented.
     *
     * @return html depending on the path, a gallery or an item is returned.
     */
    public function createGallery()
    {
        // Validate incoming arguments
        $this->validateParameters();

        // Read and present images in the current directory
        if (is_dir($this->pathToGallery)) {
          $gallery = $this->readAllItemsInDir($this->pathToGallery);
        }
        else if (is_file($this->pathToGallery)) {
          $gallery = $this->readItem($this->pathToGallery);
        }

        return $gallery;
    }

    private function validateParameters()
    {
        ($this->pathToGallery !== false) or $this->errorMessage("The path to the gallery image seems to be a non existing path.");
        ($this->basePath !== false) or $this->errorMessage("The basepath to the gallery, GALLERY_PATH, seems to be a non existing path.");
        is_dir($this->galleryPath) or $this->errorMessage('The gallery dir "' . $this->galleryPath . '" is not a valid directory.');
        substr_compare($this->basePath, $this->pathToGallery, 0, strlen($this->basePath)) == 0 or $this->errorMessage("Security constraint: Source gallery is not directly below the directory GALLERY_PATH.\n" . $this->basePath . "\n" . $this->pathToGallery);
    }

    /**
     * Display error message.
     *
     * @param  string $message the error message to display.
     * @return void.
     */
    private function errorMessage($message)
    {
        header("Status: 404 Not Found");
        die('gallery.php says 404 - ' . htmlentities($message));
    }



    /**
     * Read directory and return all items in a ul/li list.
     *
     * @param string $path to the current gallery directory.
     * @param array $validImages to define extensions on what are considered to be valid images.
     *
     * @return string html with ul/li to display the gallery.
     */
    private function readAllItemsInDir($path, $validImages = array('png', 'jpg', 'jpeg', 'gif'))
    {
        $files = glob($path . '/*');
        $gallery = "<ul class='gallery'>\n";
        $len = strlen($this->galleryPath);

        foreach($files as $file) {
            $parts = pathinfo($file);
            $href  = str_replace('\\', '/', substr($file, $len + 1));

            // Is this an image or a directory
            if (is_file($file) && in_array($parts['extension'], $validImages)) {
                $item    = "<img src='img.php?src=" . $this->galleryBaseUrl . $href . "&amp;width=128&amp;height=128&amp;crop-to-fit' alt=''/>";
                $caption = basename($file);
            }
            else if (is_dir($file)) {
                $item    = "<img src='img/folder.png' alt=''/>";
                $caption = basename($file) . '/';
            }
            else {
                continue;
            }

            // Avoid to long captions breaking layout
            $fullCaption = $caption;
            if (strlen($caption) > 18) {
                $caption = substr($caption, 0, 10) . '…' . substr($caption, -5);
            }

            $gallery .= "<li><a href='?path={$href}' title='{$fullCaption}'><figure class='figure overview'>{$item}<figcaption>{$caption}</figcaption></figure></a></li>\n";
        }

        $gallery .= "</ul>\n";

        return $gallery;
    }

    /**
     * Read and return info on choosen item.
     *
     * @param string $path to the current gallery item.
     * @param array $validImages to define extensions on what are considered to be valid images.
     *
     * @return string html to display the gallery item.
     */
    private function readItem($path, $validImages = array('png', 'jpg', 'jpeg', 'gif')) {
        $parts = pathinfo($path);
        if (!(is_file($path) && in_array($parts['extension'], $validImages))) {
            return "<p>This is not a valid image for this gallery.";
        }

        // Get info on image
        $imgInfo = list($width, $height, $type, $attr) = getimagesize($path);
        $mime = $imgInfo['mime'];
        $gmdate = gmdate("D, d M Y H:i:s", filemtime($path));
        $filesize = round(filesize($path) / 1024);

        // Get constraints to display original image
        $displayWidth  = $width > 800 ? "&amp;width=800" : null;
        $displayHeight = $height > 600 ? "&amp;height=600" : null;

        // Display details on image
        $len = strlen($this->galleryPath);
        $href = $this->galleryBaseUrl . str_replace('\\', '/', substr($path, $len + 1));
        $item = <<<EOD
            <p><img src='img.php?src={$href}{$displayWidth}{$displayHeight}' alt=''/></p>
            <p>Original image dimensions are {$width}x{$height} pixels. <a href='img.php?src={$href}'>View original image</a>.</p>
            <p>File size is {$filesize}KBytes.</p>
            <p>Image has mimetype: {$mime}.</p>
            <p>Image was last modified: {$gmdate} GMT.</p>
EOD;

        return $item;
    }

    /**
     * Create a breadcrumb of the gallery query path.
     *
     * @return string html with ul/li to display the thumbnail.
     */
    public function createBreadcrumb()
    {
        // Replaces back slash when running on Windows
        $path = str_replace('\\', '/', $this->pathToGallery);
        $parts = explode('/', trim(substr($path, strlen($this->galleryPath) + 1), '/'));
        $breadcrumb = "<ul class='breadcrumb'>\n<li><a href='?'>Hem</a> »</li>\n";

        if (!empty($parts[0])) {
            $combine = null;
            foreach($parts as $part) {
                $combine .= ($combine ? '/' : null) . $part;
                $breadcrumb .= "<li><a href='?path={$combine}'>$part</a> » </li>\n";
            }
        }

        $breadcrumb .= "</ul>\n";

        return $breadcrumb;
    }
}
