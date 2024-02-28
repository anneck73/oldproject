# UIPublic
###### Author: wizard@mealmatch.de | STATUS: WiP | VERSION: +0.2.18

Status, quick brain dump.
 
## TWIG Namspace
You can also assign several paths to the same template namespace. The order in which paths are configured is very 
important, because Twig will always load the first template that exists, starting from the first configured path.
This feature can be used as a fallback mechanism to load generic templates when the specific template doesn't exist.

See Symfony Doc: [Namespaced Paths](https://symfony.com/doc/3.4/templating/namespaced_paths.html)


### Mealmatch TWIG Namespace configuration

    paths:
        # Local templates - for the local developer to overwrite ...
        "%kernel.root_dir%/../src/Mealmatch/UIPublicBundle/Resources/views/local": "WEBUI"
        # DEV templates - For display / testing on dev-mealmatch.frbit.com
        "%kernel.root_dir%/../src/Mealmatch/UIPublicBundle/Resources/views/dev": "WEBUI"
        # STAGE templates - Ready / Finished / Approved templates
        "%kernel.root_dir%/../src/Mealmatch/UIPublicBundle/Resources/views/stage": "WEBUI"
        # LIVE templates - The current LIVE version of the template  
        "%kernel.root_dir%/../src/Mealmatch/UIPublicBundle/Resources/views/prod": "WEBUI"
        # Old Fallback path in MMWebFront
        "%kernel.root_dir%/../src/MMWebFrontBundle/Resources/views": "WEBUI"        
        
        # Specific templates - for the local developer to overwrite ...
        "%kernel.root_dir%/../src/Mealmatch/UIPublicBundle/Resources/views": "UIVariant"

Each directory has its purpose:

##### The first directory to check is views/local.
The local developer can overwrite everything in here, to see new UI elements.

##### The 2nd directory to check is views/dev.
It contains templates __ready to be shared with other developers__ and deployed to 
_mealmatch-dev-frbit.com_.

##### The 3rd directory to check is views/stage
This directory contains templates __ready for testing__ on 
_mealmatch-stage.frbit.com_.

##### The 4th directory to check is views/prod
This directory contains __LIVE templates__ displayed on 
_mealmatch.de_.

##### The 5th directory to check is MMWebFrontBundle/Resources/views/
This directory contains old LIVE templates, and will be removed asap.

### Working with TWIG Namespace "@WEBUI"

Inside of any TWIG template the namespace "@WEBUI" can be used to resolve templates. 

Examples:

    {% extends '@WEBUI/base-layout.html.twig' %}
    {% import '@WEBUI/Widgets/mealmatch.macros.html.twig' as widget %}


TWIG will then try to find the template in the following directories AND order presented:

views/local -> views/dev -> views/stage -> views/prod

##### Worksteps / Concept

1. Start new templates in view/local.
2. __MOVE__ template files to views/dev once ready for commit & push.
3. Deploy to mealmatch-dev, test and verify changes in views/dev.
4. __MOVE__ template files __from views/dev to views/stage__ once ready for a "final qualiy check" on mealmatch-stage.
5. Deploy to mealmatch-stage, final test and quality control.
6. __MOVE__ template files __from views/stage to views/prod__.
7. Deploy to mealmatch, templates are now LIVE!

When working this way ... templates will __MOVE__ from __dev__, to __stage__, to __prod__ directories.

In the END all templates should be in __views/prod__ until a new/updates templates is supposed to be developed. 

----

## Section Title
### Sub-Section
#### Sub-Sub-Section

use inside lists for more "sub-sections."

----
#### Links / References

* REFERENCE_HERE: [I'm an inline-style link with title](https://www.google.com "Google's Homepage")
