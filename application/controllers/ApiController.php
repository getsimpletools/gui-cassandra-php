<?php


use \App\Model\Config;
use Simpletools\Db\Cassandra\Doc;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . @$_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-type');
if (@$_SERVER['REQUEST_METHOD'] == 'OPTIONS')
{
	header("HTTP/1.1 200 OK");
	exit;
}

class  ApiController extends \Simpletools\Mvc\Controller
{
	protected $_request;


	public function getApiRequest()
	{
		return json_decode(file_get_contents('php://input'));
	}

	public function response($data,$code =null)
	{
		if($data['status'] != 'OK' && !$code)
		{
			header( 'HTTP/1.1 400 BAD REQUEST' );
		}
		elseif ($code == 401)
		{
			header( 'HTTP/1.1 401 Unauthorized' );
		}

		echo json_encode($data, JSON_PRETTY_PRINT);
		exit();
	}

	public function init()
	{
		\Simpletools\Page\Layout::getInstance()->disable();
		$this->disableView();
		$this->_request = $this->getApiRequest();

		if(@$this->_request->token && ENABLE_AUTH)
		{
			session_commit();
			session_id($this->_request->token);
			session_start();
		}

		if($this->getParam('controller') == 'api' && $this->getParam('action') == 'login')
		{
			try{
				$cluster  = Cassandra::cluster()
					->withContactPoints(CLUSTER_HOST)
					->withPort(CLUSTER_PORT)
					->withCredentials(@$this->_request->username, @$this->_request->password)
					->build();
				$session  = $cluster->connect();
			}catch (\Exception $e)
			{
				$this->response(
					[
						'status' => 'FAIL',
						'msg' => $e->getMessage()
					]
				);
			}

			$_SESSION['CASSANDRA_USERNAME'] = $this->_request->username;
			$_SESSION['CASSANDRA_PASSWORD'] = $this->_request->password;

			$this->response(
				[
					'status' => 'OK',
					'body' => [
						'token' => session_id(),
						'username' => $this->_request->username,
						]
				]
			);
		}
		elseif (!@$_SESSION['CASSANDRA_USERNAME'] || !@$_SESSION['CASSANDRA_PASSWORD'])
		{
			$this->response(
				[
					'status' => 'FAIL',
				], 401
			);
		}
	}

	public function loginAction(){}

	public function logoutAction()
	{
		session_destroy();
		$this->response(
			[
				'status' => 'OK',
				'body' => []
			]
		);
	}

	public function getKeyspacesAction()
	{

		$cluster  = Cassandra::cluster()
				->withContactPoints(CLUSTER_HOST)
				->withPort(CLUSTER_PORT)
				->withCredentials($_SESSION['CASSANDRA_USERNAME'], $_SESSION['CASSANDRA_PASSWORD'])
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
				->withCredentials($_SESSION['CASSANDRA_USERNAME'], $_SESSION['CASSANDRA_PASSWORD'])
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
				->withCredentials($_SESSION['CASSANDRA_USERNAME'], $_SESSION['CASSANDRA_PASSWORD'])
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
				$doc = new Doc((array)$this->_request->keys);
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
				$doc = new Doc((array)$this->_request->keys);
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
