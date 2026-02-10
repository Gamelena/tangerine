<?php
/**
 * Manejador de Requests.
 * 
 * Encapsula los $_POST, $_GET y $_FILES, codifica/decodifica los datos ingresados como html_entities UTF-8.
 * Los valores de los resultados son datos HTML.
 * 
 * Es aconsejable usar esta clase para manejar los $_GET y $_POST fuera de controladoes.
 * 
 * Para controladores Zend_Controller usar es aconsejable $this->getRequest() para los $_GET y $_POST en lugar de Gamelena_Utils_Form, usar sólo para upload de archivos,
 * ya que Zend_Controller::getRequest() ofrece mejor compatibilidad con Zend_Test_PHPUnit_ControllerTestCase.
 *
 * @package Gamelena_Utils
 * @version $Id:$
 * @since   0.1
 * 
 * @example
 * <code> 
 * $oForm = new Gamelena_Utils_Form();
 * $var = $oForm->id;//Esto equivalente a $var = $_REQUEST['id']
 * </code> 
 */
class Gamelena_Utils_Form
{
    /**
     * Forbidden Extensions
     * 
     * @var array
     */
    private $_forbiddenExtensions = array('py', 'php', 'pl', 'cgi', 'bin', 'sh');

    /**
     * 
     * @return array()
     */
    public function getForbiddenExtension()
    {
        return $this->_forbiddenExtensions;
    }

    /**
     * 
     * @param array $forbiddenExtensions
     */
    public function setForbiddenExtensions(array $forbiddenExtensions)
    {
        $this->_forbiddenExtensions = $forbiddenExtensions;
    }

    /**
     * Forbid Extension $extension
     * 
     * @param string $extension
     */
    public function forbidExtension($extension)
    {
        $this->_forbiddenExtensions[] = $extension;
    }

    /**
     * Allow upload files with extension $extension
     * 
     * @param  string $extension
     * @return boolean
     */
    public function allowExtension($extension)
    {
        $modified = false;

        foreach ($this->_forbiddenExtensions as $i => $ext) {
            if ($extension == $ext) {
                unset($this->_forbiddenExtensions[$i]);
                $modified = true;
                break;
            }
        }
        return $modified;
    }

    /**
     * 
     * @param $array array - si existe se transforma $array a objeto y se retorna, ignorando el $_REQUEST por defecto.
     * @return void
     */
    public function __construct($array = null)
    {
        if ($array) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    $temp = array();
                    foreach ($v as $k1 => $v1) {
                        $temp[$k1] = $this->encode($v1);
                    }
                    $this->$k = $temp;
                } else {
                    $this->$k = $this->encode($v);
                }
            }
        } else {
            foreach ($_GET as $k => $v) {
                if (is_array($v)) {
                    $temp = array();
                    foreach ($v as $k1 => $v1) {
                        $temp[$k1] = $this->encode($v1);
                    }
                    $this->$k = $temp;
                } else {
                    $this->$k = $this->encode($v);
                }
            }
            foreach ($_POST as $k => $v) {
                if (is_array($v)) {
                    $temp = array();
                    foreach ($v as $k1 => $v1) {
                        $temp[$k1] = $this->encode($v1);
                    }
                    $this->$k = $temp;
                } else {
                    $this->$k = $this->encode($v);
                }
            }
        }
    }
    /**
     * Cuenta los $_POST
     * @return int
     */
    public function isPost()
    {
        return count($_POST);
    }

    /**
     * Cuenta los $_GET
     * @return int
     */
    public function isGet()
    {
        return count($_GET);
    }

    /**
     * 
     * @return Gamelena_Utils_Form
     */
    public function getInstance()
    {
        static $me;
        if (!$me) {
            $me = array(
                new Gamelena_Utils_Form()
            );
        }
        return $me[0];
    }

    /**
     * Upload de archivos
     * @param string $file
     * @param string $dest
     * @param int    $maxSize
     * @param array  $list
     * @return array
     */
    public function upload($file, $dest, $maxSize = 999999999999, $list = array(), $listIsBlack = true)
    {
        if (!empty($_FILES[$file]['name']) && is_array($_FILES[$file]['name'])) {
            $info = array();
            foreach ($_FILES[$file]['name'] as $i => $f) {
                if ($_FILES[$file]['size'][$i] > 0 && $_FILES[$file]['size'][$i] < $maxSize && !in_array(substr($_FILES[$file]['name'][$i], -3, 3), $this->_forbiddenExtensions)) {
                    $fp = explode(".", $_FILES[$file]['name'][$i]);
                    $oldname = array();
                    foreach ($fp as $j => $v) {
                        if ($j < count($fp) - 1) {
                            $oldname[] = $v;
                        }
                    }
                    $oldname = implode(".", $oldname);
                    $ext = $fp[count($fp) - 1];
                    $allowed = $listIsBlack ? !in_array($ext, $list) : in_array($ext, $list);

                    $filename = substr(md5(microtime() . $_FILES[$file]['tmp_name'][$i]), 0, 8) . Gamelena_Utils_String::slugify($oldname) . ".$ext";
                    if ($allowed) {
                        if ($this->moveUploadedFile($_FILES[$file]['tmp_name'][$i], $dest . "/" . $filename)) {
                            $info[$i]['size'] = $_FILES[$file]['size'][$i];
                            $info[$i]['filename'] = $filename;
                            $info[$i]['ext'] = $ext;
                        } else {
                            Debug::write("No se pudo subir archivo {$_FILES[$file]['tmp_name'][$i]} a $dest." / ".$filename");
                            $info = false;
                        }
                    } else {
                        Debug::write("Extensión no permitida para {$_FILES[$file]['tmp_name'][$i]} a $dest." / ".$filename");
                        $info = false;
                    }
                }
            }
            return $info;
        } else {
            if ($_FILES[$file]['size'] > 0 && $_FILES[$file]['size'] < $maxSize && !in_array(substr($_FILES[$file]['name'], -3, 3), $this->_forbiddenExtensions)) {
                $fp = explode(".", $_FILES[$file]['name']);
                $oldname = array();
                foreach ($fp as $j => $v) {
                    if ($j < count($fp) - 1) {
                        $oldname[] = $v;
                    }
                }
                $oldname = implode(".", $oldname);
                $ext = $fp[count($fp) - 1];
                $filename = substr(md5(microtime() . $_FILES[$file]['tmp_name']), 0, 8) . Gamelena_Utils_String::slugify($oldname) . ".$ext";
                if ($this->moveUploadedFile($_FILES[$file]['tmp_name'], $dest . "/" . $filename)) {
                    $info = $_FILES[$file];
                    $info['filename'] = $filename;
                    $info['ext'] = $ext;
                } else {
                    Console::error("No se pudo subir archivo {$_FILES[$file]['tmp_name']} a {$dest}/{$filename}");
                    $info = false;
                }
                return $info;
            } else {
                return false;
            }
        }
    }
    /**
     * Codifica a UTF-8
     * @param $value
     * @return unknown_type
     */

    public function encode($value)
    {
        /*
        if (get_magic_quotes_gpc() == 1 && !is_array($value)) {
            $value = stripslashes($value);
        }
        */

        if (!is_array($value)) {
            if (strlen($value) <= 256) {//Si la longitud de $value es mucho más larga que esto $this->_unescape demora demasiado en procesar.
                $value = htmlentities($this->_unescape($value), ENT_QUOTES | ENT_HTML401, 'UTF-8');
            } else {
                $value = htmlentities($value, ENT_QUOTES | ENT_HTML401, 'UTF-8');
            }
        }
        return $value;
    }

    /**
     * 
     * @param string $orig
     * @param string $dest
     */
    public function moveUploadedFile($filename, $destination)
    {
        if (PHP_SAPI === 'cli') {
            return copy($filename, $destination);
        } else {
            return move_uploaded_file($filename, $destination);
        }
    }
    /**
     * 
     * @param string $source
     * @param string $iconv_to
     * @return string
     */
    private function _unescape($source, $iconv_to = 'UTF-8')
    {
        $decodedStr = '';
        $pos = 0;
        $len = !is_array($source) ? strlen($source) : 0;
        while ($pos < $len) {
            $charAt = substr($source, $pos, 1);
            if ($charAt == '%') {
                $pos++;
                $charAt = substr($source, $pos, 1);
                if ($charAt == 'u') {
                    // we got a unicode character
                    $pos++;
                    $unicodeHexVal = substr($source, $pos, 4);
                    $unicode = hexdec($unicodeHexVal);
                    $decodedStr .= $this->_code2utf($unicode);
                    $pos += 4;
                } else {
                    // we have an escaped ascii character
                    $hexVal = substr($source, $pos, 2);
                    $decodedStr .= chr(hexdec($hexVal));
                    $pos += 2;
                }
            } else {
                $decodedStr .= $this->_isUTF8($source) ? $charAt : utf8_encode($charAt);
                $pos++;
            }
        }

        if ($iconv_to != "UTF-8") {
            $decodedStr = iconv("UTF-8", $iconv_to, $decodedStr);
        }

        return $decodedStr;
    }

    /**
     * 
     * @param int $num
     * @return string
     */
    private function _code2utf($num)
    {
        if ($num < 128) {
            return chr($num);
        }
        if ($num < 2048) {
            return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
        }
        if ($num < 65536) {
            return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        }
        if ($num < 2097152) {
            return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        }
        return '';
    }

    /**
     * 
     * @param string $string
     * @return number
     */
    private function _isUTF8($string)
    {
        // from http://w3.org/International/questions/qa-forms-utf-8.html 
        return preg_match(
            '%^( 
                 [\x09\x0A\x0D\x20-\x7E]            # ASCII 
               | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte 
               |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs 
               | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte 
               |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates 
               |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3 
               | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15 
               |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16 
           )*$%xs',
            $string
        );
    }

    /**
     * 
     * @return array
     */
    public function toArray()
    {
        return (array) $this;
    }

}
