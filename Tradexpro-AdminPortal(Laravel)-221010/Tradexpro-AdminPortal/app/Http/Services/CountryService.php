<?php
namespace App\Http\Services;
use App\Model\CountryList;
use App\Http\Repositories\CountryRepository;

class CountryService extends BaseService
{
    public $model = CountryList::class;
    public $repository = CountryRepository::class;

    public function __construct()
    {
        parent::__construct($this->model,$this->repository);
    }

    public function getCountries()
    {
        return $this->object->getCountries();
    }

    public function getActiveCountries()
    {
        return $this->object->getActiveCountries();
    }

    public function statusChange($request)
    {
        try{

            $data = [
                'country_id' => $request->country_id
            ];

            $status = $this->object->statusChange($data);

            if($status)
            {
                $response = ['success' => true, 'message' => __('Country status updated successfully!')];
            }else {
                $response = ['success' => false, 'message' => __('Country status is not updated!')];
            }
            
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => __('Something went wrong')];
            storeException("statusChange",$e->getMessage());
        }

        return $response;
    }

}