<?php
namespace fw;

use fw\lib\DiDom\Document;
use Exception;
use fw\lib\DiDom\Element;
use fw\lib\MatthiasMullie\Minify\JS;
use fw\lib\MatthiasMullie\Minify\CSS;

final class Template {

	private $file;

	public $html;

	private $templates = array();

	private $filesList = array();

	private $modificationDate = 0;

	private $hasTemplateModified = false;

	public function __construct(string $path) {
		$this->file = Core::PATH_VIEW . '/' . $path;
		
		$this->html = $this->process($this->file);
		
		$this->modificationDate = $this->getModifiedDate();
	}

	private function process(string $fileName) {
		$document = new Document();
		$document->loadHtmlFile($fileName);
		
		$elements = $document->find('template-include');
		foreach ($elements as $e) {
			$src = $e->getAttribute('src');
			if (! $src) {
				throw new Exception('Existe um template-include sem o atributo src.');
			}
			
			$template = new self($src);
			$this->templates[] = $template;
			
			$html = '<htmlfragment>' . $template->html . '</htmlfragment>';
			$fragment = (new Document($html))->first('htmlfragment')->getNode();
			
			$parent = $e->parent();
			
			foreach ($fragment->childNodes as $node) {
				$parent->insertBefore(new Element($node), $e);
			}
			
			$e->remove();
		}
		
		$path = Core::PATH_BUILD . substr($this->file, strlen(Core::PATH_VIEW));
		$pathSave = $path . '-package.js';
		$this->generatePackage(JS::class, $document, 'js-package', $pathSave, 'script', array(
			'type' => 'text/javascript',
			'charset' => Project::$chatset
		));
		
		$pathSave = $path . '-package.css';
		$this->generatePackage(CSS::class, $document, 'css-package', $pathSave, 'link', array(
			'rel' => 'stylesheet',
			'type' => 'text/css'
		));
		
		return $document->html();
	}

	private function generatePackage(String $minifyClass, Document $document, String $selector, String $pathSave, String $tagName, array $attributes) {
		$pos = strripos($pathSave, '.');
		$elements = $document->find($selector);
		foreach ($elements as $i => $element) {
			$mf = new $minifyClass();
			
			$files = explode(',', $element->getAttribute('files'));
			foreach ($files as $fileName) {
				$fileName = Core::PATH_VIEW . '/' . trim($fileName);
				$this->filesList[$fileName] = filemtime($fileName);
				
				$mf->add($fileName);
			}
			
			$_pathSave = substr_replace($pathSave, '-' . $i, $pos, 0);
			
			$mf->minify($_pathSave);
			
			if ($tagName === 'script') {
				if ($element->hasAttribute('defer')) {
					$attributes['defer'] = null;
				}
				
				if ($element->hasAttribute('async')) {
					$attributes['async'] = null;
				}
				
				$attributes['src'] = $_pathSave;
			} else {
				$attributes['href'] = $_pathSave;
			}
			
			$element->replace(new Element($tagName, null, $attributes));
		}
	}

	public function hasModification(): bool {
		if ($this->getModifiedDate() > $this->modificationDate) {
			return true;
		}
		
		foreach ($this->templates as $t) {
			if ($t->hasModification()) {
				$this->hasTemplateModified = true;
				return true;
			}
		}
		
		foreach ($this->filesList as $fileName => $time) {
			if (filemtime($fileName) > $time) {
				return true;
			}
		}
		
		return false;
	}

	public function getModifiedDate(): int {
		return filemtime($this->file);
	}

	public function hasTemplateModified(): bool {
		return $this->hasTemplateModified;
	}
}

