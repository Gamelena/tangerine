<?php

/**
 * Interface que debieran implementar los modelos  
 * para ser compatibles con los flujos de AdmPortal
 * @author rodrigo
 *
 */
interface Zwei_Admin_ModelInterface
{

    public function select ();

    public function insert (array $data);

    public function update (array $data, $where);

    public function fetchAll ();

    public function fetchRow ();

}
