<?php

namespace Ubermanu\PhpElements;

class Element implements Node
{
    /**
     * List of the self-closing tags.
     */
    const SELF_CLOSING_TAGS = [
        'area',
        'base',
        'br',
        'col',
        'embed',
        'hr',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
    ];

    /**
     * @var string
     */
    protected string $tag = 'div';

    /**
     * @var string[]
     */
    protected array $attributes = [];

    /**
     * @var Node[]
     */
    protected array $children = [];

    /**
     * @var SelectorParser
     * @internal
     */
    protected SelectorParser $selectorParser;

    /**
     * @param string|null $tag
     * @param string[]|null $attributes
     * @param Node[]|null $children
     */
    public function __construct(?string $tag = null, ?array $attributes = null, ?array $children = null)
    {
        $this->selectorParser = new SelectorParser();

        if (!empty($tag)) {
            $selector = $this->selectorParser->parse($tag);
            $this->tag = $selector['tag'];
            $this->setAttribute('id', $selector['id']);
            $this->setAttribute('class', implode(' ', $selector['classes']));
            $this->addAttributes($selector['attributes']);
        }

        if (!empty($attributes)) {
            $this->addAttributes($attributes);
        }

        if (!empty($children)) {
            foreach ($children as $child) {
                $this->appendChild($child);
            }
        }
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): Element
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function addAttributes(array $attributes): Element
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setAttribute(string $name, string $value): Element
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getAttribute(string $name): ?string
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param Node $child
     * @return $this
     */
    public function appendChild(Node $child): Element
    {
        $this->children[] = $child;
        return $this;
    }

    /**
     * @param Node $child
     * @return $this
     */
    public function prependChild(Node $child): Element
    {
        array_unshift($this->children, $child);
        return $this;
    }

    /**
     * @param Node $child
     * @return $this
     */
    public function removeChild(Node $child): Element
    {
        $key = array_search($child, $this->children, true);
        if ($key !== false) {
            unset($this->children[$key]);
        }
        return $this;
    }

    /**
     * @return Node[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $output = "<{$this->tag}";
        $attributes = array_filter($this->getAttributes());

        foreach ($attributes as $key => $value) {
            $value = \htmlspecialchars($value);
            $output .= " {$key}=\"{$value}\"";
        }

        $output .= '>';

        if (!in_array($this->tag, self::SELF_CLOSING_TAGS)) {
            foreach ($this->getChildren() as $child) {
                $output .= $child->render();
            }

            $output .= "</{$this->tag}>";
        }

        return $output;
    }
}
