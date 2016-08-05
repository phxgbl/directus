<?php

namespace Directus\Files;

class Thumbnail {

	public static function generateThumbnail($localPath, $format, $thumbnailSize, $cropEnabled) {
        switch($format) {
            case 'jpg':
            case 'jpeg':
                // $img = imagecreatefromjpeg($localPath);
				$img = imagecreatefromstring($localPath);
                break;
            case 'gif':
                // $img = imagecreatefromgif($localPath);
				$img = imagecreatefromstring($localPath);
                break;
            case 'png':
                // $img = imagecreatefrompng($localPath);
				$img = imagecreatefromstring($localPath);
                break;
            case 'pdf':
            case 'psd':
            case 'tif':
            case 'tiff':
            case 'svg':
              if(extension_loaded('imagick')) {
                $image = new \Imagick();
				$image->readImageBlob($localPath);
                $image->setIteratorIndex(0);
                $image->setImageFormat('jpeg');
				$img = $image->getImageBlob();
              } else {
                return false;
              }
              break;
			      default:
				      return false;
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $x1 = 0; // used for crops
        $y1 = 0; // used for crops
        $aspectRatio = $w / $h;

        if($cropEnabled) {
            // crop to center of image
            if($aspectRatio <= 1){
                $newW = $thumbnailSize;
                $newH = $h*($thumbnailSize/$w);
                $y1 = -1 * (($newH - $thumbnailSize)/2);
            } else {
                $newH = $thumbnailSize;
                $newW = $w*($thumbnailSize/$h);
                $x1 = -1 * (($newW - $thumbnailSize)/2);
            }
        } else {
          // portrait (or square) mode, maximize height
          if ($aspectRatio <= 1) {
              $newH = $thumbnailSize;
              $newW = $thumbnailSize * $aspectRatio;
          }
          // landscape mode, maximize width
          if ($aspectRatio > 1) {
              $newW = $thumbnailSize;
              $newH = $thumbnailSize / $aspectRatio;
          }
        }

        if($cropEnabled) {
            $imgResized = imagecreatetruecolor($thumbnailSize, $thumbnailSize);
        } else {
            $imgResized = imagecreatetruecolor($newW, $newH);
        }

        // Preserve transperancy for gifs and pngs
        if ($format == 'gif' || $format == 'png') {
            imagealphablending($imgResized, false);
            imagesavealpha($imgResized,true);
            $transparent = imagecolorallocatealpha($imgResized, 255, 255, 255, 127);
            imagefilledrectangle($imgResized, 0, 0, $newW, $newH, $transparent);
        }

        imagecopyresampled($imgResized, $img, $x1, $y1, 0, 0, $newW, $newH, $w, $h);

        imagedestroy($img);
        return $imgResized;
	}

	public static function writeImage($extension, $path, $img, $quality) {
		ob_start();
		// force $path to be NULL to dump writeImage on the stream
		$path = NULL;
        switch($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($img, $path, $quality);
                break;
            case 'gif':
                imagegif($img, $path);
                break;
            case 'png':
                imagepng($img, $path);
                break;
            case 'pdf':
            case 'psd':
            case 'tif':
            case 'tiff':
            	imagejpeg($img, $path, $quality);
                break;
        }
        return ob_get_clean();
	}

}
