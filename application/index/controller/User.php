<?php
/**
 * User: Wisp X
 * Date: 2018/9/26
 * Time: 19:37
 * Link: https://gitee.com/wispx
 */

namespace app\index\controller;

use app\common\model\Images;
use think\Db;
use think\facade\Config;
use think\facade\Session;
use think\Exception;

class User extends Base
{
    public function images($keyword = '', $limit = 30)
    {
        if ($this->request->isPost()) {
            try {
                $model = $this->user->images()->order('create_time', 'desc');
                if (!empty($keyword)) {
                    $model = $model->where('pathname', 'like', "%{$keyword}%");
                }
                $images = $model->paginate($limit)->each(function ($item) {
                   $item->url = $item->url;
                    // TODO 生成缩略图
                    return $item;
                });
            } catch (Exception $e) {
                exit(dump($e->getMessage()));
            }
            return $this->success('success', null, $images);
        }
        return $this->fetch();
    }

    public function deleteImages()
    {
        if ($this->request->isPost()) {
            Db::startTrans();
            try {
                $id = $this->request->post('id');
                $deletes = []; // 需要删除的文件
                if (is_array($id)) {
                    $images = Images::all($id);
                    foreach ($images as &$value) {
                        $deletes[$value->strategy][] = $value->pathname;
                        $value->delete();
                        unset($value);
                    }
                } else {
                    $image = Images::get($id);
                    if (!$image) {
                        throw new Exception('没有找到该图片数据');
                    }
                    $deletes[$image->strategy][] = $image->pathname;
                    $image->delete();
                }
                // 是否开启软删除(开启了只删除记录，不删除文件)
                if (!$this->config['soft_delete']) {
                    $strategy = [];
                    // 实例化所有储存策略驱动
                    $strategyAll = array_keys(Config::pull('strategy'));
                    foreach ($strategyAll as $value) {
                        // 获取储存策略驱动
                         $strategy[$value] = $this->getStrategyInstance($value);
                    }

                    foreach ($deletes as $key => $val) {
                        $strategy[$key]->deletes($val);
                    }
                }
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                return $this->error($e->getMessage());
            }
            return $this->success('删除成功');
        }
    }

    public function settings()
    {
        if ($this->request->isPost()) {
            try {
                $data = $this->request->post();
                $validate = $this->validate($data, 'Users.edit');
                if (true !== $validate) {
                    throw new Exception($validate);
                }
                if ($data['password_old']) {
                    if (md5($data['password_old']) != $this->user->password) {
                        throw new Exception('原密码不正确');
                    }
                }
                if (!$data['password']) unset($data['password']);
                $this->user->save($data);
            } catch (Exception $e) {
                return $this->error($e->getMessage());
            }
            return $this->success('保存成功');
        }
        return $this->fetch();
    }

    public function logout()
    {
        Session::delete('uid');
        $this->redirect(url('/'));
    }
}