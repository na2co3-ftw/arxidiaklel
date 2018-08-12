<?php

class Form
{
	/**
	 * @var FormComponent[]
	 */
	private $components = [];

	public function radio(string $key, array $values, string $defaultValue) {
		$this->components[] = new Radio($key, $values, $defaultValue);
	}

	public function textEdit(string $key, string $defaultValue) {
		$this->components[] = new textEdit($key, $defaultValue);
	}

	public function br() {
		$this->components[] = new Br();
	}

	public function submit(string $label) {
		$this->components[] = new Submit($label);
	}


	public function render(array $param): string {
		$html = "<form action=\"./\" method=\"get\">\n";
		foreach ($this->components as $component) {
			$html .= $component->render($param);
		}
		$html .= "\n</form>\n";
		return $html;
	}

	public function getParam($get): array {
		$param = [];
		foreach ($this->components as $component) {
			$key = $component->getKey();
			if ($key) {
				$param[$key] = $component->getValue($get);
			}
		}
		return $param;
	}

	public function getComponent(string $key) {
		foreach ($this->components as $component) {
			if ($component->getKey() == $key) {
				return $component;
			}
		}
		return null;
	}
}

interface FormComponent
{
	public function getKey(): string;

	public function getValue(array $param): string;

	public function render(array $param): string;
}

class Radio implements FormComponent
{
	private $key;
	private $values;
	private $defaultValue;

	public function __construct(string $key, array $values, string $defaultValue) {
		$this->key = $key;
		$this->values = $values;
		$this->defaultValue = $defaultValue;
	}

	public function getKey(): string {
		return $this->key;
	}

	public function getValue(array $param): string {
		if (isset($param[$this->key]) && array_key_exists($param[$this->key], $this->values)) {
			return $param[$this->key];
		}
		return $this->defaultValue;
	}

	public function getLabel(string $value): string {
		return $this->values[$value] ?? null;
	}

	public function render(array $param): string {
		$inputValue = $this->getValue($param);
		$html = '';
		foreach ($this->values as $value => $label) {
			$html .= '<label><input type="radio"';
			$html .= ' name="' . $this->key . '"';
			$html .= ' value="' . $value . '"';
			if ($inputValue == $value) {
				$html .= ' checked="checked"';
			}
			$html .= '>' . $label . '</label>';
		}
		return $html;
	}
}

class TextEdit implements FormComponent
{
	private $key;
	private $defaultValue;

	public function __construct(string $key, string $defaultValue) {
		$this->key = $key;
		$this->defaultValue = $defaultValue;
	}

	public function getKey(): string {
		return $this->key;
	}

	public function getValue(array $param): string {
		if (isset($param[$this->key])) {
			return $param[$this->key];
		}
		return $this->defaultValue;
	}

	public function render(array $param): string {
		return "<input type=\"text\" name=\"{$this->key}\" value=\"{$this->getValue($param)}\">";
	}
}

class Submit implements FormComponent
{
	private $label;

	function __construct(string $label) {
		$this->label = $label;
	}

	public function getKey(): string {
		return '';
	}

	public function getValue(array $param): string {
		return '';
	}

	public function render(array $param): string {
		return "<input type=\"submit\" value=\"{$this->label}\">";
	}
}

class Br implements FormComponent
{
	public function getKey(): string {
		return '';
	}

	public function getValue(array $param): string {
		return '';
	}

	public function render(array $param): string {
		return "<br>\n";
	}
}
