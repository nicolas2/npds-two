<?php
/**
 * 
 *
 * @author 
 * @version 
 * @date 
 */
namespace npds\container;

use Closure;
use ReflectionClass;
use ReflectionParameter;


/*
 * container
 */
class container
{

	/**
	 * Enregistrez une liaison avec le conteneur.
	 * @param  sting $abstract
	 * @param  mised $concrete
	 * @return void
	 */
    public function bind($abstract, $concrete = null)
    {
        if (!$concrete instanceof Closure) 
        {
            $concrete = $this->getClosure($abstract, $concrete);
        }
        $this->bindings[$abstract] = compact('concrete');
    }

    /**
     * Obtenez la fermeture à utiliser lors de la création d'une class.
     * @param  string $abstract 
     * @param  string $concrete 
     * @return Closure
     */
    protected function getClosure($abstract, $concrete)
    {
        return function ($container) use ($abstract, $concrete)
        {
            $method = ($abstract == $concrete) ? 'build' : 'make';

            return call_user_func(array($container, $method), $concrete);
        };
    }

    /**
     * Résolvez une class donné dans une instance.
     * @param  string $abstract
     * @return mixed
     */
    public function make($abstract)
    {
        $concrete = $this->getConcrete($abstract);
        
        if ($this->isBuildable($concrete, $abstract)) 
        {
            $object = $this->build($concrete);
        } 
        else 
        {
            $object = $this->make($concrete);
        }

        return $object;
    }  

    /**
     * Obtenez le type concret d'une class donné.
     * @param  string $abstract
     * @return mixed
     */
    protected function getConcrete($abstract)
    {
        if (!isset($this->bindings[$abstract])) 
        {
            return $abstract;
        }

        return $this->bindings[$abstract]['concrete'];
    }

    /**
     * Déterminez si la class donné est instanciable.
     * @param  mixed  $concrete
     * @param  string  $abstract
     * @return boolean
     */
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    /**
     * Instanciez une instance du type donné.
     * @param  string $concrete
     * @return mixed
     */
    public function build($concrete)
    {
        if ($concrete instanceof Closure) 
        {
            return $concrete($this);
        }

        $reflector = new ReflectionClass($concrete);
        
        if (!$reflector->isInstantiable()) 
        {
            echo $message = "Error : Class [$concrete] is not instantiable.";
        }

        $constructor = $reflector->getConstructor();
        
        if (is_null($constructor)) 
        {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instances = $this->getDependencies($dependencies);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Résolvez toutes les dépendances de ReflectionParameters.
     * @param  array $parameters
     * @return array
     */
    protected function getDependencies($parameters)
    {
        $dependencies = [];
        foreach ($parameters as $parameter) 
        {
            $dependency = $parameter->getClass();
            
            if (is_null($dependency)) 
            {
                $dependencies[] = null;
            } 
            else 
            {
                $dependencies[] = $this->resolveClass($parameter);
            }
        }

        return (array) $dependencies;
    }

    /**
     * Résolvez une dépendance basée sur une classe à partir du conteneur.
     * @param  ReflectionParameter $parameter
     * @return mixed
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        return $this->make($parameter->getType()->name);
    }

}
