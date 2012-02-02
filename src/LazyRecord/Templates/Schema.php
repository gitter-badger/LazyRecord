<?php
{% set ns = schema.getNamespace %}
{% if ns %}
namespace {{ ns }};
{% endif %}

use LazyRecord\Schema;

class {{ schema.getModelName }}SchemaProxy extends Schema
{

	public function __construct()
	{
		$this->columns = {{schema_data.columns|export}};
		$this->columnNames = {{schema_data.column_names|export}};
		$this->primaryKey =  {{schema_data.primary_key|export}};
		$this->table = {{schema_data.table|export}};
		$this->modelClass = {{schema_data.model_class|export}};
	}

}
