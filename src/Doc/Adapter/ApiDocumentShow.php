<?php

declare(strict_types=1);

/*
 * This file is part of eelly package.
 *
 * (c) eelly.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eelly\Doc\Adapter;

use Eelly\Annotations\Adapter\AdapterInterface;
use GuzzleHttp\json_encode;
use ReflectionClass;

/**
 * Class ApiDocumentShow.
 */
class ApiDocumentShow extends AbstractDocumentShow implements DocumentShowInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    public function __construct(string $class, string $method)
    {
        $this->class = $class;
        $this->method = $method;
    }

    public function display(): void
    {
        $reflectionClass = new ReflectionClass($this->class);
        $interfaces = $reflectionClass->getInterfaces();
        $interface = array_pop($interfaces);
        $reflectionMethod = $interface->getMethod($this->method);

        $docComment = $reflectionMethod->getDocComment();
        $factory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
        $docblock = $factory->create($docComment);
        $summary = $docblock->getSummary();
        $description = $docblock->getDescription();
        $authors = $docblock->getTagsByName('author');
        $authorsMarkdown = '';
        foreach ($authors as $item) {
            $authorsMarkdown .= sprintf("- %s <%s>\n", $item->getAuthorName(), $item->getEmail());
        }
        $paramsDescriptions = [];
        foreach ($docblock->getTagsByName('param') as $item) {
            $paramsDescriptions[$item->getVariableName()] = (string) $item->getDescription();
        }

        $params = [];
        $paramsMarkdown = '';
        foreach ($reflectionMethod->getParameters() as $key => $value) {
            $name = $value->getName();
            $params[$key] = [
                'name'         => $name,
                'type'         => (string) $value->getType(),
                'allowsNull'   => '否',
                'defaultValue' => ' ',
                'description'  => $paramsDescriptions[$name],
            ];
            if ($value->isDefaultValueAvailable()) {
                $params[$key]['defaultValue'] = $value->getDefaultValue();
                $params[$key]['allowsNull'] = '是';
                if (null === $params[$key]['defaultValue']) {
                    $params[$key]['defaultValue'] = 'null';
                }
            }
            $paramsMarkdown .= sprintf("%s|%s|%s|%s|%s\n",
                $params[$key]['name'],
                $params[$key]['type'],
                $params[$key]['allowsNull'],
                $params[$key]['defaultValue'],
                $params[$key]['description']);
        }
        $methodMarkdown = $this->getFileContent($interface->getFileName(), $reflectionMethod->getStartLine(), 1);
        $methodMarkdown = trim($methodMarkdown);
        if ($this->annotations instanceof AdapterInterface) {
            $this->annotations->delete($reflectionMethod->class);
        }
        $annotations = $this->annotations->getMethod(
            $reflectionMethod->class,
            $reflectionMethod->name
        );

        $arguments = $annotations->get('requestExample')->getArgument(0);
        $requestExample = '';
        if (is_array($arguments)) {
            foreach ($arguments as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                $requestExample .= $key.':'.$value.PHP_EOL;
            }
        }
        $arguments = $annotations->get('returnExample')->getArgument(0);
        $returnExample = json_encode(['data' => $arguments], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $markdown = <<<EOF
## $summary

$description

### 请求参数
参数名|类型|是否可选|默认值|说明
-----|----|-----|-------|---
$paramsMarkdown

### 接口原型
```php
$methodMarkdown
```

### 请求示例
```json
$requestExample
```

### 返回示例
```json
$returnExample    
```

### 代码贡献
$authorsMarkdown


EOF;
        $this->echoMarkdownHtml($markdown);
    }
}