<?php

/**
 * AppserverIo\Apps\Example\Services\SchemaProcessor
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io-apps/example
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Apps\Example\Services;

use Doctrine\ORM\Tools\SchemaTool;
use AppserverIo\Apps\Example\Entities\Product;

/**
 * A singleton session bean implementation that handles the
 * schema data for Doctrine by using Doctrine ORM itself.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io-apps/example
 * @link      http://www.appserver.io
 *
 * @Stateless
 */
class SchemaProcessor extends AbstractProcessor implements SchemaProcessorInterface
{

    /**
     * The DIC provider instance.
     *
     * @var \AppserverIo\Appserver\DependencyInjectionContainer\Interfaces\ProviderInterface $provider
     * @Resource(name="ProviderInterface")
     */
    protected $providerInterface;

    /**
     * The default username.
     *
     * @var string
     */
    const DEFAULT_USERNAME = 'appserver';

    /**
     * Example method that should be invoked after constructor.
     *
     * @return void
     * @PostConstruct
     */
    public function initialize()
    {
        $this->getInitialContext()->getSystemLogger()->info(
            sprintf('%s has successfully been invoked by @PostConstruct annotation', __METHOD__)
        );
    }

    /**
     * Deletes the database schema and creates it new.
     *
     * Attention: All data will be lost if this method has been invoked.
     *
     * @return void
     */
    public function createSchema()
    {

        // load the entity manager and the schema tool
        $entityManager = $this->getEntityManager();
        $schemaTool = new SchemaTool($entityManager);

        // load the class definitions
        $classes = $entityManager->getMetadataFactory()->getAllMetadata();

        // drop the schema if it already exists and create it new
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);
    }

    /**
     * Creates the default credentials to login.
     *
     * @return void
     */
    public function createDefaultCredentials()
    {

        try {
            // load the entity manager
            $entityManager = $this->getEntityManager();

            // set user data and save it
            $user = $this->providerInterface->newInstance('\AppserverIo\Apps\Example\Entities\User');
            $user->setEmail('info@appserver.io');
            $user->setUsername(SchemaProcessor::DEFAULT_USERNAME);
            $user->setUserLocale('en_US');
            $user->setPassword(md5('appserver.i0'));
            $user->setEnabled(true);
            $user->setRate(1000);
            $user->setContractedHours(160);
            $user->setLdapSynced(false);
            $user->setSyncedAt(time());

            // persist the user
            $entityManager->persist($user);

            // flush the entity manager
            $entityManager->flush();

        } catch (\Exception $e) {
            // log the exception
            $this->getInitialContext()->getSystemLogger()->error($e->__toString());
        }
    }
}
