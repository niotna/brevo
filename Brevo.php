<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Brevo;

use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Thelia\Install\Database;
use Thelia\Model\Config;
use Thelia\Model\ConfigQuery;
use Thelia\Module\BaseModule;

/**
 * Class Brevo
 * @package Brevo
 * @author Chabreuil Antoine <achabreuil@openstudio.com>
 */
class Brevo extends BaseModule
{
    const MESSAGE_DOMAIN = "brevo";

    const CONFIG_NEWSLETTER_ID = "brevo.newsletter_id";
    const CONFIG_API_SECRET = "brevo.api.secret";
    const CONFIG_THROW_EXCEPTION_ON_ERROR = "brevo.throw_exception_on_error";

    public function postActivation(ConnectionInterface $con = null): void
    {
        $con->beginTransaction();

        try {
            if (null === ConfigQuery::read(static::CONFIG_API_SECRET)) {
                $this->createConfigValue(static::CONFIG_API_SECRET, [
                    "fr_FR" => "Secret d'API pour brevo",
                    "en_US" => "Api secret for brevo",
                ]);
            }

            if (null === ConfigQuery::read(static::CONFIG_NEWSLETTER_ID)) {
                $this->createConfigValue(static::CONFIG_NEWSLETTER_ID, [
                    "fr_FR" => "ID de la liste de diffusion brevo",
                    "en_US" => "Diffusion list ID of brevo",
                ]);
            }

            $database = new Database($con);
            $database->insertSql(null, [__DIR__ . "/Config/thelia.sql"]);

            $con->commit();
        } catch (\Exception $e) {
            $con->rollBack();

            throw $e;
        }
    }

    protected function createConfigValue($name, array $translation, $value = '')
    {
        $config = new Config();
        $config
            ->setName($name)
            ->setValue($value)
        ;

        foreach ($translation as $locale => $title) {
            $config->getTranslation($locale)
                ->setTitle($title)
            ;
        }

        $config->save();
    }


    /**
     * @param string $currentVersion
     * @param string $newVersion
     * @param ConnectionInterface $con
     */
    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
    {
        if ($newVersion === '1.3.2') {
            $db = new Database($con);

            $tableExists = $db->execute("SHOW TABLES LIKE 'brevo_newsletter'")->rowCount();

            if ($tableExists) {
                // Le champ relation ID change de format.
                $db->execute("ALTER TABLE `brevo_newsletter` CHANGE `relation_id` `relation_id` varchar(255) NOT NULL AFTER `email`");
            }
        }
    }

    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR.ucfirst(self::getModuleCode()).'/I18n/*'])
            ->autowire(true)
            ->autoconfigure(true);
    }
}
