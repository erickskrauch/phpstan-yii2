<?php
declare(strict_types=1);

namespace Proget\PHPStan\Yii2;

use Closure;
use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;
use RuntimeException;

final class ServiceMap {

    /**
     * @var array<string, string>
     */
    private array $services = [];

    /**
     * @var array<string, string>
     */
    private array $components = [];

    /**
     * @throws RuntimeException|ReflectionException
     */
    public function __construct(string $configPath) {
        if (!file_exists($configPath)) {
            throw new InvalidArgumentException(sprintf('Provided config path %s must exist', $configPath));
        }

        defined('YII_DEBUG') || define('YII_DEBUG', true);
        defined('YII_ENV_DEV') || define('YII_ENV_DEV', false);
        defined('YII_ENV_PROD') || define('YII_ENV_PROD', false);
        defined('YII_ENV_TEST') || define('YII_ENV_TEST', true);

        $config = require $configPath;
        foreach ($config['container']['singletons'] ?? [] as $id => $definition) {
            $this->services[$id] = $this->guessDefinition($id, $definition);
        }

        foreach ($config['container']['definitions'] ?? [] as $id => $definition) {
            $this->services[$id] = $this->guessDefinition($id, $definition);
        }

        foreach ($config['components'] ?? [] as $id => $definition) {
            $this->components[$id] = $this->guessDefinition($id, $definition);
        }
    }

    public function getServiceClassFromNode(Node $node): ?string {
        if ($node instanceof String_ && isset($this->services[$node->value])) {
            return $this->services[$node->value];
        }

        return null;
    }

    public function getComponentClassById(string $id): ?string {
        return $this->components[$id] ?? null;
    }

    /**
     * @param object|string|Closure|array{class?: string, __class?: string} $definition
     * @throws RuntimeException|ReflectionException
     */
    private function guessDefinition(string $id, $definition): string {
        if (is_string($definition) && class_exists($definition)) {
            return $definition;
        }

        if ($definition instanceof Closure) {
            $returnType = (new ReflectionFunction($definition))->getReturnType();
            if ($returnType instanceof ReflectionNamedType) {
                return $returnType->getName();
            }

            if (class_exists($id)) {
                return $id;
            }

            throw new RuntimeException(sprintf('Please provide return type for %s service closure', $id));
        }

        if (is_object($definition)) {
            return get_class($definition);
        }

        if (is_array($definition)) {
            if (isset($definition['class'])) {
                return $definition['class'];
            }

            if (isset($definition['__class'])) {
                return $definition['__class'];
            }
        }

        if (class_exists($id)) {
            return $id;
        }

        throw new RuntimeException(sprintf('Unsupported definition for %s', $id));
    }

}
