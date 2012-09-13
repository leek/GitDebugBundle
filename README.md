# LeekGitDebugBundle

Adds useful Git information to the Symfony2 debug toolbar.

##### Current branch in toolbar (Symfony 2.1)

![Example Toolbar](http://i.imgur.com/Sa6z0.png)

##### Branch list in menu

![Example Menu #1](http://i.imgur.com/A7qZk.png)

##### Tag list in menu

![Example Menu #2](http://i.imgur.com/hEss5.png)

## Installation **(Symfony 2.0.x only)**

##### 1. Add the following to your `deps` file:

```ini
    [LeekGitDebugBundle]
        git=git://github.com/leek/GitDebugBundle.git
        target=bundles/Leek/GitDebugBundle
        version=origin/2.0
```
    
##### 2. Run the vendors install script:

```
php bin/vendors install
```
    
##### 3. Add the `Leek` namespace to `app/autoload.php`:

```php
<?php
// ...
    $loader->registerNamespaces(array(
        // ...
        'Leek'                         => __DIR__.'/../vendor/bundles',
```
      
##### 4. Setup the bundle to load only on your `dev` or `test` environment(s) in `app/appKernel.php`:
   
```php
<?php
// ...
    if (in_array($this->getEnvironment(), array('dev', 'test'))) {
        // ...
        $bundles[] = new Leek\GitDebugBundle\LeekGitDebugBundle();
```
      
That's it! You should now see your current Git branch on the debug toolbar.

> **Note:** A `composer.json` file is also provided if you prefer to use Composer. The `require` key is: [leek/git-debug-bundle](http://packagist.org/packages/leek/git-debug-bundle)