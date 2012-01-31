# LeekGitDebugBundle

Adds useful Git information to the Symfony2 debug toolbar.

Currently, this bundle adds the following:

 * Current branch name to toolbar
 * Full branch list at debug menu
 * Full tag list at debug menu

![Example Toolbar](http://i.imgur.com/ewaiC.png)

## Installation

##### 1. Add the following to you `deps` file:

    [LeekGitDebugBundle]
        git=https://leek@github.com/leek/GitDebugBundle.git
        target=bundles/Leek/GitDebugBundle
    
##### 2. Run the vendors install script:

    php bin/vendors install
    
##### 3. Add the `Leek` namespace to `app/autoload.php`:

    $loader->registerNamespaces(array(
        // ...
        'Leek'                         => __DIR__.'/../vendor/bundles',
        
##### 4. Setup the bundle to load only on your `dev` or `test` environment(s). Edit `app/appKernel.php`:
   
    if (in_array($this->getEnvironment(), array('dev', 'test'))) {
        // ...
        $bundles[] = new Leek\GitDebugBundle\LeekGitDebugBundle();
        
That's it! You should now see your current Git branch on the debug toolbar.