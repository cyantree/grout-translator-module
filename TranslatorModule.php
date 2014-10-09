<?php
namespace Grout\Cyantree\TranslatorModule;

use Cyantree\Grout\App\Module;
use Grout\Cyantree\TranslatorModule\Types\TranslatorConfig;
use Zend\Cache\Storage\Adapter\Filesystem;
use Zend\Cache\Storage\Adapter\FilesystemOptions;
use Zend\Cache\Storage\Plugin\Serializer;
use Zend\I18n\Translator\Translator;

class TranslatorModule extends Module
{
    /** @var Translator */
    public $translator;

    /** @var TranslatorConfig */
    public $moduleConfig;

    public function init()
    {
        $this->app->configs->setDefaultConfig($this->id, new TranslatorConfig(), $this);

        $this->translator = $translator = new Translator();

        /** @var TranslatorConfig $config */
        $this->moduleConfig = $config = $this->app->configs->getConfig($this->id);

        if (!$this->app->getConfig()->developmentMode) {
            $c = new Filesystem();
            $o = new FilesystemOptions();
            $o->setCacheDir($this->app->cacheStorage->createStorage($this->id));
            $c->setOptions($o);
            $c->addPlugin(new Serializer());
            $translator->setCache($c);
        }

        $translator->setLocale($config->defaultLanguage);

        $folder = $this->app->parseUri($config->translationsDirectory);
        foreach ($config->contexts as $context => $file) {
            if (is_int($context)) {
                $context = $file;
                $file .= '.mo';
            }
            $translator->addTranslationFilePattern('gettext', $folder, '%s/' . $file, $context);
        }

        $this->translator = $translator;
    }
}
