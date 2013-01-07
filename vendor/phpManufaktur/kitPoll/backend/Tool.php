<?php

namespace phpmanufaktur\kitPoll\Backend;

// need the I18n service
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

// require Twig
require_once VENDOR_PATH.'/Twig/Autoloader.php';
\Twig_Autoloader::register();


class Tool {

  protected $translator = NULL;
  protected $twig = NULL;

  public function __construct() {
    // initialize the translator
    $this->translator = new Translator('de_DE', new MessageSelector());
    $this->translator->setFallbackLocale('de');
    $this->translator->addLoader('array', new ArrayLoader());
    $this->translator->addResource('array', array(
        'Hello {{ name }}!' => 'Hallo {{ name }}!',
    ), 'de');

    // initialize Twig
    $loader = new \Twig_Loader_String();
    $this->twig = new \Twig_Environment($loader);

  }

  public function Hello() {

    echo $this->twig->render($this->translator->trans('Hello {{ name }}!'), array('name' => 'Ralf'));
  }
} // class Dialog

