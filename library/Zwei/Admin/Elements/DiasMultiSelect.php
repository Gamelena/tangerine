<?php
/**
 * Setea un input con un valor numérico según dias de la semana escogido, 
 *
 * Depende de tabla semana_dias que asocia un día un valor potencia de dos, 
 * el valor final es el total de la suma de cada día seleccionado.
 *
 * Ejemplo:
 * <code>
 * <field name="D&amp;iacute;as" target="dias_aplicacion" formatter="formatDias" type="dias_multi_select" required="true" trim="true" visible="true" edit="true" add="true"/>
 * </code>
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */

class Zwei_Admin_Elements_DiasMultiSelect extends Zwei_Admin_Elements_Element
{
	public function edit($i, $j, $display="inline")
	{
		//Zwei_Utils_Debug::write($this);
		$out="
		<input type=\"hidden\" name=\"$this->target[$i]\" id=\"edit{$i}_{$j}\" value=\"$this->value\"/>
		<table>
			<select multiple=\"true\" id=\"dias{$i}_{$j}\" style=\"height:150px;width:160px\" dojoType=\"dojox.form.CheckedMultiSelect\" onChange=\"setDias(this.get('id'), 'edit{$i}_{$j}')\">";

		$SemanaDias = new SemanaDiasModel();
		$oSelect = $SemanaDias->select()->order('id DESC');
		$oSelect->where('id != ?', '1');
		Zwei_Utils_Debug::writeBySettings($oSelect->__toString(), 'query_log');		
		$result = $SemanaDias->fetchAll($oSelect);

		foreach ($result as $r){
			$out .= "<option value=\"{$r['id']}\">{$r['value']}</option>";
		}
		$out .= " </select>
		</table>
		";
		return $out;
	}

	public function display($i, $j)
	{
		return "";
	}
	
	public function editCustomDisplay($i, $j)
	{
        return "
        var opciones = dijit.byId('dias{$i}_{$j}').getOptions();
        opciones = configurarDiasChequeados(opciones, items[0].{$this->target});
        console.debug('opciones: '+ opciones);
        dijit.byId('dias{$i}_{$j}').updateOption(opciones);";		
	}	
}
