<?php
namespace GF\CheckoutHelper;

class CheckoutHelper
{
    private $pib = '';
    private $billing_company_name = '';

    private function get_pib_value(){
        if (wc()->session->get('gf_billing_pib')) {
            $this->pib = wc()->session->get('gf_billing_pib');
            return true;
        }
        return false;
    }

    private function get_billing_company_name(){
        if (wc()->session->get('gf_billing_company')) {
            $this->pib = wc()->session->get('gf_billing_company');
            return true;
        }
        return false;
    }

    public function create_custom_meta()
    {
        if ($this->get_pib_value()){
            wc()->customer->update_meta_data('gf_billing_pib',$this->pib );
        }
        if ($this->get_billing_company_name()){
            wc()->customer->update_meta_data('gf_billing_company_name',$this->billing_company_name);
        }

    }

    public function set_user_meta($user_id)
    {
        if ($user_id){
            $customer = new \WC_Customer($user_id);
            $this->set_billing_pib($customer);
            $this->set_billing_company($customer);
        }
    }

    protected function set_billing_pib($customer)
    {
        $customer->update_meta_data('billing_pib',$this->pib);
        wc()->session->__unset('gf_billing_pib');

    }

    protected function set_billing_company($customer)
    {
        $customer->set_billing_company($this->billing_company_name);
        wc()->session->__unset('$gf_billing_company');
    }
}