<?php

  date_default_timezone_set('America/Sao_Paulo');
  session_start();

  class CropAvatar {
    private $src;
    private $data;
    private $file;
    private $dst;
    private $type;
    private $extension;
    private $msg;

    const AVATAR_SAVE_PATH = '../avatar/';

    function __construct($src, $data, $file) {
      $this -> setSrc($src);
      $this -> setData($data);
      $this -> setFile($file);
      $this -> crop($this -> src, $this -> dst, $this -> data);
    }

    private function setSrc($src) {
      if (!empty($src)) {
        $type = exif_imagetype($src);

        if ($type) {
          $this -> src = $src;
          $this -> type = $type;
          $this -> extension = image_type_to_extension($type);
          $this -> setDst();
        }
      }
    }

    private function setData($data) {
      if (!empty($data)) {
        $this -> data = json_decode(stripslashes($data));
      }
    }

    private function setFile($file) {
      $errorCode = $file['error'];

      if ($errorCode === UPLOAD_ERR_OK) {
        $type = exif_imagetype($file['tmp_name']);

        if ($type) {
          $extension = image_type_to_extension($type);
          $src = self::AVATAR_SAVE_PATH . $_SESSION["UsrId"] . '.original' . $extension;

          if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG) {

            if (file_exists($src)) {
              unlink($src);
            }

            $result = move_uploaded_file($file['tmp_name'], $src);

            if ($result) {
              $this -> src = $src;
              $this -> type = $type;
              $this -> extension = $extension;
              $this -> setDst();
            } else {
               $this -> msg = 'Falha no upload';
            }
          } else {
            $this -> msg = 'Por favor, escolha uma imagem dos seguintes tipos: JPG, PNG, GIF';
          }
        } else {
          $this -> msg = 'Por favor, envie um arquivo de imagem';
        }
      } else {
        $this -> msg = $this -> codeToMessage($errorCode);
      }
    }

    private function setDst() {
      $this -> dst = self::AVATAR_SAVE_PATH . $_SESSION["UsrId"] . '.png';
    }

    private function crop($src, $dst, $data) {
      if (!empty($src) && !empty($dst) && !empty($data)) {
        switch ($this -> type) {
          case IMAGETYPE_GIF:
            $src_img = imagecreatefromgif($src);
            break;

          case IMAGETYPE_JPEG:
            $src_img = imagecreatefromjpeg($src);
            break;

          case IMAGETYPE_PNG:
            $src_img = imagecreatefrompng($src);
            break;
        }

        if (!$src_img) {
          $this -> msg = "Falha na leitura da imagem";
          return;
        }

        $size = getimagesize($src);
        $size_w = $size[0]; // natural width
        $size_h = $size[1]; // natural height

        $src_img_w = $size_w;
        $src_img_h = $size_h;

        $degrees = $data -> rotate;

        // Rotate the source image
        if (is_numeric($degrees) && $degrees != 0) {
          // PHP's degrees is opposite to CSS's degrees
          $new_img = imagerotate( $src_img, -$degrees, imagecolorallocatealpha($src_img, 0, 0, 0, 127) );

          imagedestroy($src_img);
          $src_img = $new_img;

          $deg = abs($degrees) % 180;
          $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

          $src_img_w = $size_w * cos($arc) + $size_h * sin($arc);
          $src_img_h = $size_w * sin($arc) + $size_h * cos($arc);

          // Fix rotated image miss 1px issue when degrees < 0
          $src_img_w -= 1;
          $src_img_h -= 1;
        }

        $tmp_img_w = $data -> width;
        $tmp_img_h = $data -> height;
        $dst_img_w = 141;
        $dst_img_h = 106;

        $src_x = $data -> x;
        $src_y = $data -> y;

        if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
          $src_x = $src_w = $dst_x = $dst_w = 0;
        } else if ($src_x <= 0) {
          $dst_x = -$src_x;
          $src_x = 0;
          $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
        } else if ($src_x <= $src_img_w) {
          $dst_x = 0;
          $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
        }

        if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
          $src_y = $src_h = $dst_y = $dst_h = 0;
        } else if ($src_y <= 0) {
          $dst_y = -$src_y;
          $src_y = 0;
          $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
        } else if ($src_y <= $src_img_h) {
          $dst_y = 0;
          $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
        }

        // Scale to destination position and size
        $ratio = $tmp_img_w / $dst_img_w;
        $dst_x /= $ratio;
        $dst_y /= $ratio;
        $dst_w /= $ratio;
        $dst_h /= $ratio;

        $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);

        // Add transparent background to destination image
        imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
        imagesavealpha($dst_img, true);

        $result = imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

        if ($result) {
          if (!imagepng($dst_img, $dst)) {
            $this -> msg = "Falha ao salvar a imagem cortada";
          }
        } else {
          $this -> msg = "Falha ao cortar a imagem";
        }

        imagedestroy($src_img);
        imagedestroy($dst_img);
      }
    }

    private function codeToMessage($code) {
      switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
          $message = 'A imagem selecionada excede o máximo permitido';
          break;

        case UPLOAD_ERR_FORM_SIZE:
          $message = 'A imagem selecionada excede o máximo permitido no formulário';
          break;

        case UPLOAD_ERR_PARTIAL:
          $message = 'A imagem foi enviada parcialmente';
          break;

        case UPLOAD_ERR_NO_FILE:
          $message = 'Nenhum arquivo enviado';
          break;

        case UPLOAD_ERR_NO_TMP_DIR:
          $message = 'Faltando pasta temporária';
          break;

        case UPLOAD_ERR_CANT_WRITE:
          $message = 'Falha de escrita no disco';
          break;

        case UPLOAD_ERR_EXTENSION:
          $message = 'O envio foi interrompido pela extensão';
          break;

        default:
          $message = 'Erro desconhecido';
      }

      return $message;
    }

    public function getResult() {
      return !empty($this -> data) ? $this -> dst : $this -> src;
    }

    public function getMsg() {
      return $this -> msg;
    }
  }

  $crop = new CropAvatar($_POST['avatar_src'], $_POST['avatar_data'], $_FILES['avatar_file']);

  $response = array(
    'state'  => 200,
    'message' => $crop -> getMsg(),
    'result' => $crop -> getResult()
  );

  echo json_encode($response);
?>
