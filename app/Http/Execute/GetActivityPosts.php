<?php

namespace App\Http\Execute;

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$id = $argv[1];

require 'Execute.php';

class GetActivityPosts extends Execute
{
    public $file;
    public $id;
    public $access_token;
    public $data = array();
    public $token;
    public $loading = 0;
    public $countGroups;
    public $i = 0;
    public $iter = 0;
    public $checked = 0;
    public $allCount = 0;

    public function pause()
    {
        if (++$this->i >= 2) { $this->i = 0; sleep(1); }
    }

    public function parse($request)
    {
		$res = $this->checkRN();
        $posts = explode($res, $this->getData());


        $data = [];
        function vkapi($method, $params) {
            $params = http_build_query($params);
            $result = json_decode(file_get_contents('https://api.vk.com/method/'. $method .'?' . $params), true);

			if (isset($result['response'])) {
            	return $result['response'];
			}
        }

        $count = count($posts);

		if ($posts)
		{
            $i = 0;
            $iter = 0;
			foreach($posts as $post)
			{

                // Вычисляем процент
                $percent = round(($iter++ / $count) * 100);
                $this->setPercent($percent);

                $pos = strpos(substr($post, 0, 65), "wall");

                if ($pos !== false) {
    				// Чистим ссылки. Находим owner id и item id
                    $post = strstr($post, 'wall');
                    $post = str_replace('wall', '', $post);
                    $post = str_replace('%2Fall', '', $post);
                }

				//$post_arr = preg_split("/\||\_/m",$post_id);   - альтернативный способ
				$post_arr = explode('_', $post);

				if (!$post) continue;

				// Получаем лайки
				if (isset($request['likes']))
				{

					$request_params = array(
						'type'         => 'post',
						'owner_id'     => $post_arr[0],
						'item_id'      => $post_arr[1],
						'offset'       => 0,
						'count'        => 1000,
						'v'            => '5.130',
						'access_token' => $this->access_token,
					);

					do {
						if (++$i > 2) { sleep(1); $i = 0; }
						$result = vkapi('likes.getList', $request_params);

						$request_params['offset'] = $request_params['offset'] + 1000;

						if (isset($result['items'])) {
							if (count($result['items']) > 0)
                            	file_put_contents($this->file, implode("\n", $result['items']) . "\n", FILE_APPEND);
							//$data = array_merge($data, $result['items']);

						} else break;

					} while($request_params['offset'] <= $result['count']);
				}

				// Собираем комментарии
				if (isset($request['comments']))
				{

					$request_params = array(
						'type'         => 'post',
						'owner_id'     =>  $post_arr[0],
						'post_id'      =>  $post_arr[1],
						'offset'       => 0,
						'count'        => 100,
						'v'            => '5.130',
						'access_token' => $this->access_token,
					);

					do {
						if (++$i > 2) { sleep(1); $i = 0; }
						$result = vkapi('wall.getComments', $request_params);
						$request_params['offset'] = $request_params['offset'] + 100;
						if (!isset($result['count'])) break;

						if ($result)
						foreach ($result['items'] as $id)
						{
							if ($id['from_id'] > 0)
								//$data[] = $id['from_id'];
                                file_put_contents($this->file, $id['from_id'] . "\n", FILE_APPEND);


							// Комментарии > комментарии
							if ($id['thread'] > 0 and isset($request['thread_comments']))
							{
								$request_params2 = $request_params;
								$request_params2['offset'] = 0;
								$request_params2['comment_id'] = $id['id'];

								do {
									$i++;
									if ($i > 1) { sleep(1); $i = 0; }

									$result2 = vkapi('wall.getComments', $request_params2);
									$request_params2['offset'] = $request_params2['offset'] + 100;

									foreach ($result2['items'] as $comment2)
									if ($comment2['from_id'] > 0)
                                        file_put_contents($this->file, $comment2['from_id'] . "\n", FILE_APPEND);
										//$data[] = $comment2['from_id'];

								} while($request_params2['offset'] < $result2['count']);
							}

						}

						$i++;
						if ($i > 1) { sleep(1); $i = 0; }

					} while ($request_params['offset'] <= $result['count']);
				}
			}
		}
    }

}

$class = new GetActivityPosts($id);

$request = $class->getRequest($id);

$data = $class->parse($request);

//$data = $class->extFilter($data, $request);

$class->changeStatus($class->access_token, 'free');

//$class->writeFile($data);

$class->setPercent(100);
$class->setStatus();

die;
