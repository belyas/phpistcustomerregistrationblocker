<?php
/**
 * 2017 PHPIST
 *
 * NOTICE OF LICENSE
 *
 * @author    Yassine Belkaid <yassine.belkaid87@gmail.com>
 * @copyright 2017 PHPIST
 * @license   MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhpistCustomerRegistrationBlocker extends Module
{
    public function __construct()
    {
        $this->name = 'phpistcustomerregistrationblocker';
        $this->tab = 'front_office_features';
        $this->version = '1.2.0';
        $this->author = 'Yassine Belkaid';
        $this->need_instance = 0;

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $this->bootstrap = true;
        }

        parent::__construct();

        $this->displayName = $this->l('Block customer registration under specific age.');
        $this->description = $this->l('This module enables you to specify an age in which potential customers 
            have to obey in order to sign up in your web shop.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?');

        $this->ps_versions_compliancy = array('min' => '1.5.6.*', 'max' => _PS_VERSION_);
    }

    /**
     * Install necessary hooks
     *
     * @return boolean
     */
    public function install()
    {
        Configuration::updateValue('PHPIST_AGE_BLOCKER', 18);

        return parent::install()
            && $this->registerHook('actionBeforeSubmitAccount');
    }

    /**
     * Uninstall the module, which will remove all its data from the databse
     *
     * @return boolean
     */
    public function uninstall()
    {
        Configuration::deleteByName('PHPIST_AGE_BLOCKER');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';

        /**
         * If values have been submitted in the form, process.
         */
        if (Tools::isSubmit('submit'. $this->name)) {
            $this->_postValidation();

            if (!count($this->_errors)) {
                $updated = $this->_postProcess();
                $output .= $updated;
            } else {
                foreach ($this->_errors as $err) {
                    $output .= $this->displayError($err);
                }
            }
        }

        $this->context->smarty->assign(array(
            'module_dir'      => $this->_path,
            'current_version' => $this->version,
        ));

        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-user"></i>',
                        'desc' => $this->l('Enter an age that you want to restrict your cutomers 
                            when they try to register'),
                        'name' => 'PHPIST_AGE_BLOCKER',
                        'label' => $this->l('Age'),
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'id'    => $this->name."submit",
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'PHPIST_AGE_BLOCKER' => Configuration::get('PHPIST_AGE_BLOCKER', 18),
        );
    }

    /**
     * Save form data.
     */
    private function _postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        return $this->displayConfirmation($this->l('Settings updated'));
    }

    public function hookActionBeforeSubmitAccount($params)
    {
        $getDay   = (int)Tools::getValue('days', 0);
        $getMonth = (int)Tools::getValue('months', 0);
        $getYear  = (int)Tools::getValue('years', 0);
        $error    = false;
        
        // Check if user choose date of birth
        if (!$getDay && !$getMonth && !$getYear) {
            $this->context->controller->errors[] = Tools::displayError($this->l('Date of birth required'));
            $error = true;
        } elseif (!$getDay || !$getMonth || !$getYear) {
            $this->context->controller->errors[] = Tools::displayError($this->l('Invalid date of birth'));
            $error = true;
        }

        if (false === $error) {
            // grab minimum age to sign up with, and check against user's age
            $minAge     = (int)Configuration::get('PHPIST_AGE_BLOCKER', 18);
            $prepareAge = $this->getPersonAge($getDay, $getMonth, $getYear);
            $personAge  = $this->getRealAge($prepareAge);

            // if user's age is lesser than mim age assigned, show up an error
            if ($minAge > $personAge) {
                $this->context->controller->errors[] = Tools::displayError(
                    sprintf($this->l('You must be at least %d years old to register'), $minAge)
                );
            }
        }
    }

    /**
     * Calculate customer's age
     *
     * @return object
     */
    private function getPersonAge($day, $month, $year)
    {
        $date = new DateTime(sprintf('%02d', $day).'-'.sprintf('%02d', $month).'-'.$year);

        return $date;
    }

    /**
     * Get customer's year of birth
     *
     * @return int
     */
    private function getRealAge($person_age)
    {
        return $person_age->diff(new DateTime('now'))->y;
    }

    private function _postValidation()
    {
        if (Tools::isSubmit('submit'. $this->name)) {
            if (!(int)Tools::getValue('PHPIST_AGE_BLOCKER')) {
                $this->_errors[] = $this->l('Age field must not be empty and can accept numeric values.');
            }
        }
    }
}
