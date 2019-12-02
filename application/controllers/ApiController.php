<?php


use \App\Model\Game;
use \App\Model\Place;
use \App\Model\GameArchive;
use \App\Model\UserRate;
use \App\Model\Config;
use Simpletools\Db\Cassandra\Doc;

header('Content-Type: application/json');

class  ApiController extends \Simpletools\Mvc\Controller
{
	protected $_request;

	public function getApiRequest()
	{
		return json_decode(file_get_contents('php://input'));
	}

	public function response($data)
	{
		if($data['status'] != 'OK')
		{
			header( 'HTTP/1.1 400 BAD REQUEST' );
		}

		echo json_encode($data, JSON_PRETTY_PRINT);
		exit();
	}

	public function init()
	{
		\Simpletools\Page\Layout::getInstance()->disable();
		$this->disableView();
		$this->_request = $this->getApiRequest();
	}

	public function getKeyspacesAction()
	{

		$cluster  = Cassandra::cluster()
				->withContactPoints(CLUSTER_HOST)
				->withPort(CLUSTER_PORT)
				->withCredentials(CASSANDRA_USERNAME, CASSANDRA_PASSWORD)
				->build();
		//$keyspace  = 'feed_products';
		$session  = $cluster->connect();

		//$statement = new Cassandra\SimpleStatement("SELECT * FROM user;");
		$statement = new Cassandra\SimpleStatement("SELECT * FROM system_schema.keyspaces; ");
		$rows = $session->execute($statement);

		$keyspaces = [];
		foreach ($rows as $row)
		{
			$keyspaces[] = $row['keyspace_name'];
		}

		$this->response(
				[
						'status' => 'OK',
						'body' => ['keyspaces' => $keyspaces]
				]
		);
	}

	public function getTablesAction()
	{
		$cluster  = Cassandra::cluster()
				->withContactPoints(CLUSTER_HOST)
				->withPort(CLUSTER_PORT)
				->withCredentials(CASSANDRA_USERNAME, CASSANDRA_PASSWORD)
				->build();
		$session  = $cluster->connect();



		$schema   = $session->schema();
		$keyspaces = $schema->keyspaces();;


		$result = [];

		$keyspace = $schema->keyspace($this->_request->keyspace);


		$tables = $keyspace->tables();
		foreach ($tables as $tableName => $table)
		{
			$item = [
					'name' => $tableName,
				'indexes' => []
			];
			foreach ($table->columns() as $column)
			{
				$item['columns'][$column->name()] = (string)$column->type();
			}

			foreach ($table->partitionKey() as $column)
			{
				$item['partitionKey'][$column->name()] = (string)$column->type();
			}

			foreach ($table->primaryKey() as $column)
			{
				$item['primaryKey'][$column->name()] = (string)$column->type();
			}

			foreach ($table->indexes() as $column)
			{
				$item['indexes'][$column->target()] = $column->name();
			}

			foreach ($table->clusteringKey() as $column)
			{
				$item['clusteringKey'][$column->name()] = (string)$column->type();
			}

			$item['clusteringOrder'] = $table->clusteringOrder() ;

			$result[] = $item;
		}


		$this->response(
				[
						'status' => 'OK',
						'body' => ['tables' => $result]
				]
		);
	}

	public function getTableAction()
	{
		$cluster  = Cassandra::cluster()
				->withContactPoints(CLUSTER_HOST)
				->withPort(CLUSTER_PORT)
				->withCredentials(CASSANDRA_USERNAME, CASSANDRA_PASSWORD)
				->build();
		$session  = $cluster->connect();

		$schema   = $session->schema();

		$keyspace = $schema->keyspace($this->_request->keyspace);

		$table = $keyspace->table($this->_request->table);
		$item = [
				'name' => $this->_request->table,
				'indexes' => []
		];
		foreach ($table->columns() as $column)
		{
			$item['columns'][$column->name()] = (string)$column->type();
		}

		foreach ($table->partitionKey() as $column)
		{
			$item['partitionKey'][$column->name()] = (string)$column->type();
		}

		foreach ($table->primaryKey() as $column)
		{
			$item['primaryKey'][$column->name()] = (string)$column->type();
		}

		foreach ($table->indexes() as $column)
		{
			$item['indexes'][$column->target()] = $column->name();
		}

		foreach ($table->clusteringKey() as $column)
		{
			$item['clusteringKey'][$column->name()] = (string)$column->type();
		}

		$item['clusteringOrder'] = $table->clusteringOrder() ;

		$this->response(
				[
						'status' => 'OK',
						'body' => ['table' => $item]
				]
		);
	}

	public function getTableDataAction()
	{
		if($this->_request)
		{
			try
			{

				$query = new \Simpletools\Db\Cassandra\Query($this->_request->table, $this->_request->keyspace);

				if($this->_request->keys)
				{
					$keys = (array)$this->_request->keys;
					foreach ($keys as $k => $v)
						if($v === null || $v==='') unset($keys[$k]);
				}



				if (@$keys)
				{
					$keys = (array)$this->_request->keys;
					$query->where(key($keys), array_shift($keys));

					foreach ($keys as $key => $val)
					{
						$query->also($key, $val);
					}
				}
				else
				{
					$query->allowFiltering();
				}

				$data = $query->limit($this->_request->limit)
						->run()
						->fetchAll();


				$this->response(
						[
								'status' => 'OK',
								'body' => ['tableData' => $data]
						]
				);
			} catch (Exception $e)
			{
				$this->response(
						[
								'status' => 'FAIL',
								'msg' => $e->getMessage()
						]
				);
			}
		}
	}

	public function setTableItemAction()
	{
		if($this->_request)
		{
			try{
				$doc = new Doc($this->_request->keys);
				$doc->keyspace($this->_request->keyspace)
						->table($this->_request->table)
						->body($this->_request->item)
						->save();

				$this->response(
						[
								'status' => 'OK'
						]
				);

			}catch (Exception $e)
			{
				$this->response(
						[
								'status' => 'FAIL',
								'msg' => $e->getMessage()
						]
				);
			}
		}
	}

	public function removeTableItemAction()
	{
		if($this->_request)
		{
			try{
				$doc = new Doc($this->_request->keys);
				$doc->keyspace($this->_request->keyspace)
						->table($this->_request->table)
						->remove();

				$this->response(
						[
								'status' => 'OK'
						]
				);

			}catch (Exception $e)
			{
				$this->response(
						[
								'status' => 'FAIL',
								'msg' => $e->getMessage()
						]
				);
			}
		}
	}

}

?>
