<?php
declare(strict_types=1);

namespace ErickSkrauch\PHPStan\Yii2;

use Closure;
use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use ReflectionFunction;
use ReflectionNamedType;
use RuntimeException;

/**
 * @phpstan-type Yii2Definition object|string|Closure|array{class?: string, __class?: string}
 * @phpstan-type YII2ContainerConfig array{
 *     container?: array{
 *         singletons?: array<string, Yii2Definition>,
 *         definitions?: array<string, Yii2Definition>,
 *     },
 *     components?: array<string, Yii2Definition>,
 * }
 */
final class ServiceMap {

    private const CORE_COMPONENTS = [
        'log' => \yii\log\Dispatcher::class,
        'view' => \yii\web\View::class,
        'formatter' => \yii\i18n\Formatter::class,
        'i18n' => \yii\i18n\I18N::class,
        'urlManager' => \yii\web\UrlManager::class,
        'assetManager' => \yii\web\AssetManager::class,
        'security' => \yii\base\Security::class,
        // TODO: Maybe in the future there will be a configuration which environment we want to analyze: web or console
        'request' => \yii\web\Request::class,
        'response' => \yii\web\Response::class,
        'session' => \yii\web\Session::class,
        'user' => \yii\web\User::class,
        'errorHandler' => \yii\web\ErrorHandler::class,
    ];

    /**
     * @var array<string, string>
     */
    private array $services = [];

    /**
     * @var array<string, string>
     */
    private array $components = [];

    /**
     * @throws \RuntimeException
     * @throws \ReflectionException
     */
    public function __construct(string $configPath) {
        if (!file_exists($configPath)) {
            throw new InvalidArgumentException(sprintf('Provided config path %s must exist', $configPath));
        }

        defined('YII_DEBUG') || define('YII_DEBUG', true);
        defined('YII_ENV_DEV') || define('YII_ENV_DEV', false);
        defined('YII_ENV_PROD') || define('YII_ENV_PROD', false);
        defined('YII_ENV_TEST') || define('YII_ENV_TEST', true);

        /** @var YII2ContainerConfig $config */
        $config = require $configPath;
        foreach ($config['container']['singletons'] ?? [] as $id => $definition) {
            $this->services[$id] = $this->guessDefinition($id, $definition, $config);
        }

        foreach ($config['container']['definitions'] ?? [] as $id => $definition) {
            $this->services[$id] = $this->guessDefinition($id, $definition, $config);
        }

        foreach ($config['components'] ?? [] as $id => $definition) {
            $this->components[$id] = $this->guessDefinition($id, $definition, $config);
        }
    }

    public function getServiceClassFromNode(Node $node): ?string {
        if ($node instanceof String_) {
            $service = $node->value;
        } elseif ($node instanceof ClassConstFetch && $node->class instanceof Name) {
            $service = $node->class->toString();
        } else {
            return null;
        }

        return $this->services[$service] ?? null;
    }

    public function getComponentClassById(string $id): ?string {
        return $this->components[$id] ?? self::CORE_COMPONENTS[$id] ?? null;
    }

    /**
     * @param Yii2Definition $definition
     * @param YII2ContainerConfig $config
     *
     * @throws \RuntimeException
     * @throws \ReflectionException
     */
    private function guessDefinition(string $id, $definition, $config): string {
        if ($definition instanceof Closure) {
            $returnType = (new ReflectionFunction($definition))->getReturnType();
            if ($returnType instanceof ReflectionNamedType) {
                return $returnType->getName();
            }

            if (class_exists($id) || interface_exists($id)) {
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

            if (isset(self::CORE_COMPONENTS[$id])) {
                return self::CORE_COMPONENTS[$id];
            }
        }

        if (is_string($definition)) {
            if (isset($config['container']['definitions'][$definition])) {
                return $this->guessDefinition($definition, $config['container']['definitions'][$definition], $config);
            }

            if (isset($config['container']['singletons'][$definition])) {
                return $this->guessDefinition($definition, $config['container']['singletons'][$definition], $config);
            }

            if (class_exists($definition) || interface_exists($definition)) {
                return $definition;
            }
        }

        if (class_exists($id)) {
            return $id;
        }

        throw new RuntimeException(sprintf('Unsupported definition for %s', $id));
    }

}
