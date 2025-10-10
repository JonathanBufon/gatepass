<?php


namespace GatePass\Core;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader as ContainerLoader;
use Symfony\Component\Routing\Loader\PhpFileLoader as RouteLoader;
use Symfony\Component\Routing\RouteCollection;
use Exception;

/**
 * Kernel é a classe central da aplicação.
 * Sua responsabilidade é inicializar todos os componentes,
 * construir o container de serviços e carregar a coleção de rotas.
 */
class Kernel
{
    private string $projectDir;
    private ?ContainerBuilder $container = null;
    private ?RouteCollection $routeCollection = null;

    /**
     * O construtor recebe o diretório raiz do projeto para
     * conseguir localizar os arquivos de configuração.
     * @param string $projectDir O caminho absoluto para a pasta 'backend'.
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * O método principal que inicializa a aplicação.
     * Ele orquestra a configuração e compilação do container e das rotas.
     */
    public function boot(): void
    {
        $this->configureContainer();

        // A compilação do container é um passo de otimização crucial.
        // Ele resolve todas as dependências e cria uma versão em cache
        // para máxima performance.
        $this->container->compile();

        $this->configureRoutes();
    }

    /**
     * Retorna o container de serviços já construído e compilado.
     * @return ContainerBuilder
     * @throws Exception se o boot() não tiver sido chamado.
     */
    public function getContainer(): ContainerBuilder
    {
        if (null === $this->container) {
            throw new Exception("O Kernel não foi inicializado. Chame o método boot() primeiro.");
        }
        return $this->container;
    }

    /**
     * Retorna a coleção de rotas da aplicação.
     * @return RouteCollection
     * @throws Exception se o boot() não tiver sido chamado.
     */
    public function getRouteCollection(): RouteCollection
    {
        if (null === $this->routeCollection) {
            throw new Exception("O Kernel não foi inicializado. Chame o método boot() primeiro.");
        }
        return $this->routeCollection;
    }

    /**
     * Método privado que carrega o arquivo de configuração de serviços
     * e monta o container de injeção de dependência.
     */
    private function configureContainer(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new ContainerLoader($this->container, new FileLocator($this->projectDir . '/config'));
        $loader->load('services.php');
    }

    /**
     * Método privado que carrega o arquivo de configuração de rotas
     * e monta a coleção de rotas da aplicação.
     */
    private function configureRoutes(): void
    {
        $fileLocator = new FileLocator($this->projectDir . '/config');
        $loader = new RouteLoader($fileLocator);
        $this->routeCollection = $loader->load('routes.php');
    }
}
