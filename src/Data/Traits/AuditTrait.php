<?php

namespace PMRAtk\Data\Traits;

trait AuditTrait {

    /*
     * use in Model::init() to add the audit Ref
     */
    protected function _addAuditRef() {
        $this->hasMany('Audit', [
            function() {
                return (new \PMRAtk\Data\Audit($this->persistence, ['parentObject' => $this]))->addCondition('model_class', get_class($this));
            },
            'their_field' => 'model_id']);

        //after save, create Audit
        $this->addHook('afterSave', function($m, $is_update) {
            $m->createAudit($is_update ? 'CHANGE' : 'CREATE');
        });

        //after delete, create Audit
        $this->addHook('afterDelete', function($m) {
            $m->createDeleteAudit();
        });
    }


    /*
     * usually returns $this->ref('Audit'). May be overwritten by descendants
     * to add a more complex Audit model (e.g. Coupons and AccountingItems)
     */
    public function getAuditViewModel() {
        return $this->ref('Audit');
    }


    /*
     * if this function returns false, no audit is created. Defaults to checking
     * ENV setting, but can be overweritten on a per class basis
     */
    protected function _auditEnabled():bool {
        if(!isset($_ENV['CREATE_AUDIT']) || !$_ENV['CREATE_AUDIT']) {
            return false;
        }

        return true;
    }


    /*
     * Save any change to Audit
     */
    public function createAudit($type) {
        if(!$this->_auditEnabled()) {
            return;
        }

        $audit = new \PMRAtk\Data\Audit($this->persistence, ['parentObject' => $this]);
        $audit->set('value', $type);

        $data = [];
        foreach($this->dirty as $field_name => $dirty_field) {
            //only audit non system fields and fields that go to persistence
            if(!$this->hasField($field_name)
                || $this->getField($field_name)->system
                ||$this->getField($field_name)->never_persist) {
                continue;
            }
            //check if any "real" value change happened
            if($dirty_field === $this->get($field_name)) {
                continue;
            }
            //strings special treatment
            //money due to GermanMoneyFormatFieldTrait
            if(in_array($this->getField($field_name)->type, ['string', 'text', 'money'])
                && $dirty_field == $this->get($field_name)) {
                continue;
            }

            //time fields
            if($this->getField($field_name)->type === 'time') {
                $data[$field_name] = $this->_timeFieldAudit($field_name, $dirty_field);
            }
            //date fields
            elseif($this->getField($field_name)->type === 'date') {
                $data[$field_name] = $this->_dateFieldAudit($field_name, $dirty_field);
            }
            //hasOne relationship
            elseif($this->hasRef($field_name) && $this->getRef($field_name) instanceOf \atk4\data\Reference\HasOne) {
                $old = $this->ref($field_name)->newInstance();
                $old->tryLoad($dirty_field);
                $new = $this->ref($field_name)->newInstance();
                $new->tryLoad($this->get($field_name));
                $data[$field_name] = $this->_hasOneAudit($field_name, $dirty_field, $new, $old);
            }
            //dropdowns
            elseif(isset($this->getField($field_name)->ui['form']) && in_array('DropDown', $this->getField($field_name)->ui['form'])) {
                $data[$field_name] = $this->_dropDownAudit($field_name, $dirty_field);
            }
            //any other field
            else {
                $data[$field_name] = $this->_normalFieldAudit($field_name, $dirty_field);
            }

        }
        if($type == 'CREATE' || $data) {
            $audit->set('data', $data);
            $audit->save();
        }
    }


    /*
     * save delete to Audit
     */
    public function createDeleteAudit() {
        if(!$this->_auditEnabled()) {
            return;
        }
        $audit = new \PMRAtk\Data\Audit($this->persistence, ['parentObject' => $this]);
        $audit->set('value', 'DELETE');
        $audit->save();
    }


    /*
     * creates an Audit for secondary models like emails, if it was added, changed or removed
     */
    public function addSecondaryAudit(
        string $type,
        \PMRAtk\Data\SecondaryBaseModel $model,
        string $field = 'value',
        string $modelClass = null,
        int $modelId = null
    ) {
        if(!$this->_auditEnabled()) {
            return;
        }

        $audit = new \PMRAtk\Data\Audit($this->persistence, ['parentObject' => $this]);
        $audit->set('value', $type.'_'.strtoupper((new \ReflectionClass($model))->getShortName()));
        if($modelClass && $modelId) {
            $audit->set('model_class', $modelClass);
            $audit->set('model_id', $modelId);
        }

        $data = [];
        //only save if some value is there or some change happened
        if($model->get($field) || isset($model->dirty[$field])) {
            $data = ['old_value' => (isset($model->dirty[$field]) ? $model->dirty[$field] : ''), 'new_value' => $model->get($field)];
        }
        if($data) {
            $audit->set('data', $data);
            $audit->save();
        }
    }


    /*
     * creates an Audit for adding/removing MToM Relations
     */
    public function addMToMAudit(string $type, \PMRAtk\Data\BaseModel $model, $nameField = 'name') {
        if(!$this->_auditEnabled()) {
            return;
        }

        $audit = new \PMRAtk\Data\Audit($this->persistence, ['parentObject' => $this]);
        $audit->set('value', $type.'_'.strtoupper((new \ReflectionClass($model))->getShortName()));

        $data = ['id' => $model->get('id'), 'name' => $model->get($nameField), 'model' => get_class($model)];

        $audit->set('data', $data);
        $audit->save();
    }


    /*
     * Adds an additional audit entry which is not related to one of the model's fields
     */
    public function addAdditionalAudit(string $type, array $data) {
        if(!$this->_auditEnabled()) {
            return;
        }
        $audit = new \PMRAtk\Data\Audit($this->persistence, ['parentObject' => $this]);
        $audit->set('value', $type);
        $audit->set('data', $data);
        $audit->save();
    }


    /*
     *  used to create a array containing the audit data for a normal field
     *
     * @param string  | the name of the field
     * @param string  | the old value of this field
     *
     * @return array
     */
    private function _normalFieldAudit($field_name, $dirty_field):array {
        return [
            'field_name' => $this->getField($field_name)->getCaption(),
            'old_value'  => $dirty_field,
            'new_value'  => $this->get($field_name),
        ];
    }


    /*
     *  used to create a array containing the audit data for a date field
     *
     * @param string  | the name of the field
     * @param object|null | the old date of this field as DateTime object
     *
     * @return array
     */
    private function _dateFieldAudit($field_name, $dirty_field):array {
        return [
            'field_name' => $this->getField($field_name)->getCaption(),
            'old_value'  => ($dirty_field instanceof \DateTime) ? date_format($dirty_field, 'd.m.Y') : '',
            'new_value'  => ($this->get($field_name) instanceof \DateTime) ? date_format($this->get($field_name), 'd.m.Y') : '',
        ];
    }


    /*
     *  used to create a array containing the audit data for a time field
     *
     * @param string  | the name of the field
     * @param object|null | the old date of this field as DateTime object
     *
     * @return array
     */
    private function _timeFieldAudit($field_name, $dirty_field):array {
        return [
            'field_name' => $this->getField($field_name)->getCaption(),
            'old_value'  => ($dirty_field instanceof \DateTime) ? date_format($dirty_field, 'H:i') : '',
            'new_value'  => ($this->get($field_name) instanceof \DateTime) ? date_format($this->get($field_name), 'H:i') : '',
        ];
    }


    /*
     * used to create a array containing the audit data for a one to many relation field
     *
     * @param string        the name of the field
     * @param mixed         the old value of the field
     * @param object        the object for the new value
     * @param object        the object for the old value
     *
     * @return array
     */
    private function _hasOneAudit($field_name, $dirty_field, $o_new, $o_old):array {
        //both objects loaded, means field had a value before and now
        if($o_new->loaded() && $o_old->loaded()) {
            return [
                'field_name' => $this->getField($field_name)->getCaption(),
                'old_value'  => $o_old->get('name'),
                'new_value'  => $o_new->get('name'),
            ];
        }
        //only new object loaded, means field didnt have a value before
        elseif($o_new->loaded()) {
            return [
                'field_name' => $this->getField($field_name)->getCaption(),
                'old_value'  => $dirty_field,
                'new_value'  => $o_new->get('name'),
            ];
        }
        else {
            return [
                'field_name' => $this->getField($field_name)->getCaption(),
                'old_value'  => $dirty_field,
                'new_value'  => $this->get($field_name),
            ];
        }
    }


    /**
     *
     */
    private function _dropDownAudit(string $field_name, $dirty_field):array {
        $old_value = $new_value = '...';
        if(isset($this->getField($field_name)->values[$dirty_field])) {
            $old_value = $this->getField($field_name)->values[$dirty_field];
        }
        elseif(isset($this->getField($field_name)->ui['form']['values'][$dirty_field])) {
            $old_value = $this->getField($field_name)->ui['form']['values'][$dirty_field];
        }
        elseif(isset($this->getField($field_name)->ui['form']['empty'])) {
            $old_value = $this->getField($field_name)->ui['form']['empty'];
        }

        if(isset($this->getField($field_name)->values[$this->get($field_name)])) {
            $new_value = $this->getField($field_name)->values[$this->get($field_name) ];
        }
        elseif(isset($this->getField($field_name)->ui['form']['values'][$this->get($field_name)])) {
            $new_value = $this->getField($field_name)->ui['form']['values'][$this->get($field_name) ];
        }
        elseif(isset($this->getField($field_name)->ui['form']['empty'])) {
            $new_value = $this->getField($field_name)->ui['form']['empty'];
        }
        return [
            'field_name' => $this->getField($field_name)->getCaption(),
            'old_value'  => $old_value,
            'new_value'  => $new_value,
        ];
    }
}