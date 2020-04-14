<?php
/**
 * 产品管理
 * @author Chen<cxd032404@hotmail.com>
 */

class Xrace_ProductController extends AbstractController
{
	/**商品:Product
	 * @var string
	 */
	protected $sign = '?ctl=xrace/product';
	/**
	 * game对象
	 * @var object
	 */
	protected $oProduct;
	protected $oRace;
	/**
	 * 初始化
	 * (non-PHPdoc)
	 * @see AbstractController#init()
	 */
	public function init()
	{
		parent::init();
		$this->oProduct = new Xrace_Product();
		$this->oRace = new Xrace_Race();
	}
	//商品类型列表页面
	public function indexAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//对应赛事ID
			$RaceCatalogId = isset($this->request->RaceCatalogId)?intval($this->request->RaceCatalogId):0;
			//获取赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0);
			//获取商品类型列表
			$ProductTypeArr = $this->oProduct->getProductTypeList($RaceCatalogId);
			//初始空的产品类型列表
			$ProductTypeList = array();
			//循环产品类型列表
			foreach($ProductTypeArr as $ProductTypeId => $ProductTypeInfo)
			{
				//获取产品类型信息
				$ProductTypeList[$ProductTypeInfo['RaceCatalogId']]['ProductTypeList'][$ProductTypeId] = $ProductTypeInfo;
				//计算商品类型数量
				$ProductTypeList[$ProductTypeInfo['RaceCatalogId']]['ProductTypeCount'] = isset($ProductTypeList[$ProductTypeInfo['RaceCatalogId']]['ProductTypeCount'])?$ProductTypeList[$ProductTypeInfo['RaceCatalogId']]['ProductTypeCount']+1:1;
				//如果对应赛事有配置
				if(isset($RaceCatalogList[$ProductTypeInfo['RaceCatalogId']]))
				{
					//获取赛事名称
					$ProductTypeList[$ProductTypeInfo['RaceCatalogId']]['RaceCatalogName'] = $RaceCatalogList[$ProductTypeInfo['RaceCatalogId']]['RaceCatalogName'];
				}
				else
				{
					$ProductTypeList[$ProductTypeInfo['RaceCatalogId']]['RaceCatalogName'] = 	"未定义";
				}
			}
			//模版渲染
			include $this->tpl('Xrace_Product_ProductTypeList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加产品配置填写配置页面
	public function productTypeAddAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductTypeInsert");
		if($PermissionCheck['return'])
		{
			//赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0);
			//渲染模板
			include $this->tpl('Xrace_Product_ProductTypeAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加新产品类型
	public function productTypeInsertAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductTypeInsert");
		if($PermissionCheck['return'])
		{
			//获取页面参数
			$bind=$this->request->from('ProductTypeName','RaceCatalogId');
			//商品类型名称不能为空
			if(trim($bind['ProductTypeName'])=="")
			{
				$response = array('errno' => 1);
			}
			//必须选择一个赛事
			elseif(intval($bind['RaceCatalogId'])==0)
			{
				$response = array('errno' => 2);
			}
			else
			{
				$res = $this->oProduct->insertProductType($bind);
				$response = $res ? array('errno' => 0) : array('errno' => 9);
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//修改商品类型页面
	public function productTypeModifyAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductTypeModify");
		if($PermissionCheck['return'])
		{
			//赛事列表
			$RaceCatalogList  = $this->oRace->getRaceCatalogList(0,"*",0);
			//商品类型ID
			$ProductTypeId = intval($this->request->ProductTypeId);
			//获取商品类型信息
			$ProductTypeInfo = $this->oProduct->getProductType($ProductTypeId,'*');
			//渲染模板
			include $this->tpl('Xrace_Product_ProductTypeModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//更新商品类型
	public function productTypeUpdateAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductTypeModify");
		if($PermissionCheck['return'])
		{

			//获取页面参数
			$bind=$this->request->from('ProductTypeId','ProductTypeName','RaceCatalogId');
			//商品类型名称不能为空
			if(trim($bind['ProductTypeName'])=="")
			{
				$response = array('errno' => 1);
			}
			//必须选择一个赛事
			elseif(intval($bind['RaceCatalogId'])==0)
			{
				$response = array('errno' => 2);
			}
			else
			{
				$res = $this->oProduct->updateProductType($bind['ProductTypeId'],$bind);
				$response = $res ? array('errno' => 0) : array('errno' => 9);
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//删除产品类型信息
	public function productTypeDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductTypeDelete");
		if($PermissionCheck['return'])
		{
			$ProductTypeId = trim($this->request->ProductTypeId);
			$this->oProduct->deleteProductType($ProductTypeId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//商品列表
	public function productListAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//产品类型ID
			$ProductTypeId = trim($this->request->ProductTypeId);
			//获取商品类型信息
			$ProductTypeInfo = $this->oProduct->getProductType($ProductTypeId,'ProductTypeId,ProductTypeName');
			//获取产品列表
			$ProductList = $this->oProduct->getAllProductList($ProductTypeId);
			//循环产品列表
			foreach($ProductList[$ProductTypeId] as $k => $ProductInfo)
			{
				$ProductSkuList = $this->oProduct->getAllProductSkuList($ProductInfo['ProductId'],"ProductSkuId,ProductSkuName,ProductId");
				$t = array();
				if(count($ProductSkuList[$ProductInfo['ProductId']]))
				{
					foreach($ProductSkuList[$ProductInfo['ProductId']] as $ProductSkuId => $ProductSkuInfo)
					{
						$t[] = $ProductSkuInfo['ProductSkuName'];
					}
				}
				$ProductList[$ProductTypeId][$k]['ProductSkuList'] = count($t)?implode('/',$t):"";
				//如果有获取到压缩数组
				if(isset($ProductInfo['comment']))
				{
					//数据解包
					$ProductList[$ProductTypeId][$k]['comment'] = json_decode($ProductInfo['comment'],true);
				}
			}
			//渲染模板
			include $this->tpl('Xrace_Product_ProductList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加商品界面
	public function productAddAction()
	{
		//产品类型ID
		$ProductTypeId = trim($this->request->ProductTypeId);
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductInsert");
		if($PermissionCheck['return'])
		{
		   //渲染模板
			include $this->tpl('Xrace_Product_ProductAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加商品
	public function productInsertAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductInsert");
		if($PermissionCheck['return'])
		{
			//获取页面参数
			$bind=$this->request->from('ProductName','ProductTypeId');
			//商品名称不能为空
			if(trim($bind['ProductName'])=="")
			{
				$response = array('errno' => 1);
			}
			else
			{
				//添加产品
				$res = $this->oProduct->insertProduct($bind);
				$response = $res ? array('errno' => 0) : array('errno' => 9);
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//修改商品页面
	public function productModifyAction()
	{
		//商品类型
		$ProductTypeId = trim($this->request->ProductTypeId);
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductModify");
		if($PermissionCheck['return'])
		{
			//产品ID
			$ProductId = trim($this->request->ProductId);
			//获取产品信息
			$ProductInfo = $this->oProduct->getProduct($ProductId);
			//数据解包
			$ProductInfo['comment'] = json_decode($ProductInfo['comment'],true);
			//渲染模板
			include $this->tpl('Xrace_Product_ProductModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //更新产品
	public function productUpdateAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductModify");
		if($PermissionCheck['return'])
		{
			//获取页面参数
			$bind=$this->request->from('ProductId','ProductName');
			//商品类型名称不能为空
			if(trim($bind['ProductName'])=="")
			{
				$response = array('errno' => 1);
			}
			else
			{
				//更新商品
				$res = $this->oProduct->updateProduct($bind['ProductId'], $bind);
				$response = $res ? array('errno' => 0) : array('errno' => 9);
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
    //删除产品
	public function productDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductDelete");
		if($PermissionCheck['return'])
		{
			$ProductId = trim($this->request->ProductId);
			$this->oProduct->deleteProduct($ProductId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//商品列表
	public function productSkuListAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission(0);
		if($PermissionCheck['return'])
		{
			//产品ID
			$ProductId = trim($this->request->ProductId);
			//获取商品信息
			$ProductInfo = $this->oProduct->getProduct($ProductId,'ProductId,ProductName');
			//获取产品列表
			$ProductSkuList = $this->oProduct->getAllProductSkuList($ProductId);
			//循环产品SKU列表
			foreach($ProductSkuList[$ProductId] as $k => $ProductSkuInfo)
			{
				//如果有获取到压缩数组
				if(isset($ProductSkuInfo['comment']))
				{
					//数据解包
					$ProductSkuList[$ProductId][$k]['comment'] = json_decode($ProductSkuInfo['comment'],true);
				}
			}
			//渲染模板
			include $this->tpl('Xrace_Product_ProductSkuList');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//修改商品页面
	public function productSkuModifyAction()
	{
		//商品
		$ProductSkuId = trim($this->request->ProductSkuId);
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductModify");
		if($PermissionCheck['return'])
		{
			//产品ID
			$ProductSkuId = trim($this->request->ProductSkuId);
			//获取SKU信息
			$ProductSkuInfo = $this->oProduct->getProductSku($ProductSkuId);
			//数据解包
			$ProductSkuInfo['comment'] = json_decode($ProductSkuInfo['comment'],true);
			//渲染模板
			include $this->tpl('Xrace_Product_ProductSkuModify');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//更新产品
	public function productSkuUpdateAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductModify");
		if($PermissionCheck['return'])
		{
			//获取页面参数
			$bind=$this->request->from('ProductSkuId','ProductId','ProductSkuName','ProductSkuComment');
			//商品类型名称不能为空
			if(trim($bind['ProductSkuName'])=="")
			{
				$response = array('errno' => 1);
			}
			else
			{
				//保存规格列表
				$bind['comment']['ProductSkuComment'] = trim($bind['ProductSkuComment']);
				$bind['comment'] = json_encode($bind['comment']);
				unset($bind['ProductSkuComment']);
				//更新商品
				$res = $this->oProduct->updateProductSku($bind['ProductSkuId'], $bind);
				$response = $res ? array('errno' => 0) : array('errno' => 9);
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//删除产品类型信息
	public function productSkuDeleteAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductModify");
		if($PermissionCheck['return'])
		{
			$ProductSkuId = trim($this->request->ProductSkuId);
			$this->oProduct->deleteProductSku($ProductSkuId);
			$this->response->goBack();
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加商品界面
	public function productSkuAddAction()
	{
		//产品类型ID
		$ProductId = trim($this->request->ProductId);
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductModify");
		if($PermissionCheck['return'])
		{
			//渲染模板
			include $this->tpl('Xrace_Product_ProductSkuAdd');
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
	//添加商品
	public function productSkuInsertAction()
	{
		//检查权限
		$PermissionCheck = $this->manager->checkMenuPermission("ProductModify");
		if($PermissionCheck['return'])
		{
			//获取页面参数
			$bind=$this->request->from('ProductId','ProductSkuName','ProductSkuComment');
			//商品名称不能为空
			if(trim($bind['ProductSkuName'])=="")
			{
				$response = array('errno' => 1);
			}
			else
			{
				//保存规格列表
				$bind['comment']['ProductSkuComment'] = trim($bind['ProductSkuComment']);
				$bind['comment'] = json_encode($bind['comment']);
				unset($bind['ProductSkuComment']);
				//添加产品
				$res = $this->oProduct->insertProductSku($bind);
				$response = $res ? array('errno' => 0) : array('errno' => 9);
			}
			echo json_encode($response);
			return true;
		}
		else
		{
			$home = $this->sign;
			include $this->tpl('403');
		}
	}
}
