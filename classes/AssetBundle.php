<?php
namespace app\classes;

class AssetBundle extends \yii\web\AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [];

    public $js = [];

    public $templates = [];


    public function publish($am)
    {
//        return parent::publish($am);

        if ($this->sourcePath !== null && !isset($this->basePath, $this->baseUrl)) {
            list ($this->basePath, $this->baseUrl) = $am->publish($this->sourcePath, $this->publishOptions);
        }

        $jsList = [];
        $cssList = [];

        $jsFiles = [];
        $cssFiles = [];
        $templatesFiles = [];

        foreach ($this->js as $js) {
            if (strpos($js, '://') !== false) {
                $jsList[] = $js;
            } elseif (strpos($js, '/') === 0) {
                $jsList[] = substr($js, 1) . '?' . filemtime($this->basePath . $js);
            } else {
                $jsFiles['/' . $js] = filemtime($this->basePath . '/' . $js);
            }
        }

        foreach ($this->css as $css) {
            if (strpos($css, '://') !== false) {
                $cssList[] = $css;
            } elseif (strpos($css, '/') === 0) {
                $cssList[] = substr($css, 1)  . '?' . filemtime($this->basePath . $css);
            } else {
                $cssFiles['/' . $css] = filemtime($this->basePath . '/' . $css);
            }
        }

        foreach ($this->templates as $tpl) {
            $templatesFiles['/' . $tpl] = filemtime($this->basePath . '/' . $tpl);
        }

        if (!empty($jsFiles)) {
            $fileName = 'assets/' . md5(implode('', array_values($jsFiles))) . '.js';
            $jsList[] = $fileName;
            $fileName = '/' . $fileName;
            if (!file_exists($this->basePath . $fileName)) {
                require_once __DIR__  . '/JSMin.php';
                $content = '';
                foreach ($jsFiles as $file => $v) {
                    $fileContent = file_get_contents($this->basePath . $file);
                    if (strpos($file, '.min.') !== false) {
                        $content .=  $fileContent . ';';
                    } else {
                        $minified = \JSMin::minify($fileContent);
                        $content .=  ($minified === false ? $fileContent : $minified) . ';';
                    }
                }
                file_put_contents($this->basePath . $fileName, $content);
            }
        }

        if (!empty($cssFiles)) {
            $fileName = 'assets/' . md5(implode('', array_values($cssFiles))) . '.css';
            $cssList[] = $fileName;
            $fileName = '/' . $fileName;
            if (!file_exists($this->basePath . $fileName)) {
                $content = '';
                foreach ($cssFiles as $file => $v) {
                    $content .= file_get_contents($this->basePath . $file) . ' ';
                }
                file_put_contents($this->basePath . $fileName, $content);
            }
        }

        if (!empty($templatesFiles)) {
            $fileName = 'assets/' . md5(implode('', array_values($templatesFiles))) . '.js';
            $jsList[] = $fileName;
            $fileName = '/' . $fileName;
            if (!file_exists($this->basePath . $fileName)) {
                $content = [];
                foreach ($templatesFiles as $file => $v) {
                    $content[$file] = file_get_contents($this->basePath . $file);
                }
                $content = 'window.templates = ' . json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';';
                $content = preg_replace('/\s{2,5}/', ' ', $content);
                $content = preg_replace('/\s{2,5}/', ' ', $content);
                file_put_contents($this->basePath . $fileName, $content);
            }
        }


        $this->js = $jsList;
        $this->css = $cssList;
    }

}