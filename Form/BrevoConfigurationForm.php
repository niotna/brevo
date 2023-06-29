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

namespace Brevo\Form;

use Brevo\Brevo;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\ConfigQuery;

/**
 * Class BrevoConfigurationForm
 * @package Brevo\Form
 * @author Chabreuil Antoine <achabreuil@openstudio.com>
 */
class BrevoConfigurationForm extends BaseForm
{
    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder attribute :
     *
     * $this->formBuilder->add("name", "text")
     *   ->add("email", "email", array(
     *           "attr" => array(
     *               "class" => "field"
     *           ),
     *           "label" => "email",
     *           "constraints" => array(
     *               new \Symfony\Component\Validator\Constraints\NotBlank()
     *           )
     *       )
     *   )
     *   ->add('age', 'integer');
     */
    protected function buildForm()
    {
        $translator = Translator::getInstance();

        $this->formBuilder
            ->add("api_key", TextType::class, array(
                "label" => $translator->trans("Api key", [], Brevo::MESSAGE_DOMAIN),
                "label_attr" => ["for" => "api_key"],
                "required" => true,
                "constraints" => array(
                    new NotBlank(),
                ),
                "data" => ConfigQuery::read(Brevo::CONFIG_API_SECRET)
            ))
            ->add("newsletter_list", TextType::class, array(
                "label" => $translator->trans("Contact list ID", [], Brevo::MESSAGE_DOMAIN),
                "required" => true,
                "constraints" => array(
                    new NotBlank(),
                ),
                "data" => ConfigQuery::read(Brevo::CONFIG_NEWSLETTER_ID)
            ))
            ->add("exception_on_errors", CheckboxType::class, array(
                "label" => $translator->trans("Throw exception on Brevo error", [], Brevo::MESSAGE_DOMAIN),
                "data" => (bool)ConfigQuery::read(Brevo::CONFIG_THROW_EXCEPTION_ON_ERROR, false),
                'required' => false,
                "label_attr" => [
                    'help' => $translator->trans(
                        "The module will throw an error if something wrong happens whan talking to Brevo. Warning ! This could prevent user registration if Brevo server is down or unreachable !",
                        [],
                        Brevo::MESSAGE_DOMAIN
                    )
                ]
            ))
        ;
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public static function getName()
    {
        return "brevo_configuration";
    }
}
