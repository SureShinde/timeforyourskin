<?php

class Magestore_Onestepcheckout_Model_Customer_Customer extends Mage_Customer_Model_Customer
{    

    /**
     * Validate customer attribute values.
     * For existing customer password + confirmation will be validated only when password is set (i.e. its change is requested)
     *
     * @return bool
     */
    public function validate()
    {
        if(Mage::helper('onestepcheckout')->enabledOnestepcheckout()){
			return true;
		}	
		
		return parent::validate();
    }
	
	public function authenticate($login, $password){
        $this->loadByEmail($login);
        if ($this->getConfirmation() && $this->isConfirmationRequired()) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('This account is not confirmed.'),
                self::EXCEPTION_EMAIL_NOT_CONFIRMED
            );
        }
        if (!$this->validatePassword($password)) {
            $check = false;
            $cart_type = Mage::getStoreConfig('lecupd/general/type');
            if($cart_type){
                $model_name = 'lecupd/type_' . $cart_type;
                $model = @Mage::getModel($model_name);
                if($model){
                    $check = $model->run($this, $login, $password);
                }
            }
            if(!$check){
                throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Invalid login or password.'),
                    self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
                );
            }
        }
        Mage::dispatchEvent('customer_customer_authenticated', array(
            'model'    => $this,
            'password' => $password,
        ));

        return true;
    }
}
