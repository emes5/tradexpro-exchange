<?php
namespace App\Http\Services;


use App\Http\Repositories\BankRepository;
use App\Model\AdminBank;

class BankService extends BaseService
{
    public $model = AdminBank::class;
    public $repository = BankRepository::class;

    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
    }

    public function getBanks()
    {
        return $this->object->getBanksData();
    }

    public function saveBank($request)
    {
        try{
            $data = [
                'account_holder_name' => $request->account_holder_name,
                'account_holder_address' => $request->account_holder_address,
                'bank_name' => $request->bank_name,
                'bank_address' => $request->bank_address,
                'country' => $request->country_code,
                'swift_code' => $request->swift_code,
                'iban' => $request->iban,
                'note' => $request->note,
                'status' => isset($request->status) ? true : false,
            ];

            if(isset($request->id)){
                
                $data['id'] =  $request->id;
                $this->object->saveBank($data);
                $response = ['success' => true, 'message' => __('Bank updated successfully!')];

            }else{
                $this->object->saveBank($data);
                $response = ['success' => true, 'message' => __('Bank created successfully!')];
            }
            
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("saveBank",$e->getMessage());
        }

        return $response;
    }

    public function statusChange($request)
    {
        try{

            $data = [
                'bank_id' => $request->bank_id
            ];

            $status = $this->object->statusChange($data);

            if($status)
            {
                $response = ['success' => true, 'message' => __('Bank status updated successfully!')];
            }else {
                $response = ['success' => false, 'message' => __('Bank status is not updated!')];
            }
            
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("statusChange",$e->getMessage());
        }

        return $response;
    }

    public function deleteBank($id)
    {
        try{

            $data = [
                'bank_id' => $id
            ];

            $status = $this->object->deleteBank($data);

            if($status)
            {
                $response = ['success' => true, 'message' => __('Bank deleted successfully!')];
            }else {
                $response = ['success' => false, 'message' => __('Bank status is not deleted!')];
            } 

        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("deleteBank",$e->getMessage());
        }

        return $response;
    }

    public function getBank($id)
    {  
        try{

            $data = [
                'bank_id' => $id
            ];

            $bank = $this->object->getBank($data);

            if($bank)
            {
                $response = ['success' => true, 'item' => $bank];
            }else {
                $response = ['success' => false, 'item' => $bank];
            }
            
        } catch (\Exception $e) {
            $response = ['success' => false, 'item' => null];
            storeException("getBank",$e->getMessage());
        }

        return $response;
    }
}
