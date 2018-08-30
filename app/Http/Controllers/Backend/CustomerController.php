<?php

namespace App\Http\Controllers\Backend;

use App\Http\Models\Backend\Customer;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class CustomerController extends BaseController
{
	protected $module_name = 'customer';
	protected $model = Customer::class;
	protected $listTitle = 'label.customer.title';
	protected $messageName = 'msg_customer';
	protected $initTableScript = 'backend.customer.initTableScript';

	protected $defaultOrderBy = 0;
	protected $defaultOrderDir = 'desc';

	public function __construct( Container $app )
	{
		parent::__construct( $app );
		$this->fieldList = [
			[
				'name'        => 'id',
				'title'       => '#',
				'filter_type' => '#',
				'width'       => '2%'
			],
			[
				'name'        => 'fullname',
				'title'       => 'label.customer.fullname',
				'filter_type' => 'text',
				'width'       => '15%',
			],
			[
				'name'        => 'email',
				'title'       => 'label.customer.email',
				'filter_type' => 'text',
				'width'       => '15%',
			],
			[
				'name'        => 'wallet_btc',
				'title'       => 'label.customer.wallet_btc',
				'filter_type' => 'text',
				'width'       => '15%',
			],
			[
				'name'        => 'tel',
				'title'       => 'label.customer.tel',
				'filter_type' => 'text',
				'width'       => '15%',
			],
			[
				'name'        => 'status',
				'title'       => 'label.customer.status',
				'filter_type' => '#',
				'className'   => 'text-center',
				'width'       => '4%',
			],
			[
				'name'        => '_',
				'title'       => '',
				'filter_type' => '#',
				'width'       => '8%'
			]
		];
	}

	public function _createIndexData( $items )
	{
		$data = [];
		foreach ( $items as $item ) {
			$row = [];
			foreach ( $this->fieldList as $field ) {
				if ( array_key_exists( 'relation', $field ) ) {
					if ( $field['relation']['type'] == 1 ) {
						try {
							$row[] = $item->{$field['relation']['object']}->{$field['relation']['display']};
						} catch ( \Exception $e ) {
							Log::error( $e->getMessage() );
							$row[] = '';
						}
					} else {
						$row[] = implode( ', ', $item->{$field['relation']['object']}->pluck( $field['relation']['display'] )->all() );
					}

				} else {
					if ( $field['name'] == '_' ) {
						$row[] = 'x';
					} elseif ( $field['filter_type'] == 'date-range' ) {
						// display date data in localize format
						$row[] = Carbon::parse( $item->{$field['name']} )->format( __( 'label.date_format' ) );
					} else {
						$row[] = $item->{$field['name']};
					}
				}
			}

			$data[] = $row;
		}

		return $data;
	}

	public function delete( $id )
	{
		$ids = explode( ",", $id );
		Customer::destroy( $ids );
		\Session::flash( 'msg_game', __( 'messages.delete_success' ) );

		return redirect()->route( 'backend.customer.index' );
	}

	public function reverseStatus(Request $request)
	{
		$response = [];
		$item = Customer::findOrFail($request->id);
		if($item) {
			if($item->status == 1) {
				$response['rm'] = 'fa-check-circle';
				$response['add'] = 'fa-question';
				$item->status = 0;
			} else {
				$response['add'] = 'fa-check-circle';
				$response['rm'] = 'fa-question';
				$item->status = 1;
			}
			$item->save();
		} else {
			$response['error'] = true;
		}

		return response()->json($response);
	}

	public function edit(Request $request, $id) 
	{
		$customer = Customer::find($id);
		if($customer) {
			if ($request->isMethod('post')) {
	            /*validate data request*/
	            Validator::make($request->all(), [
	                'fullname' => 'required|max:255',
	                'email' => 'required|email|max:255',
	                'tel' => 'required|max:50',
	                'wallet_btc' => 'required|max:150',
	                'country' => 'required',
	                'address' => 'max:255',
	                /*'portraitimage' => 'required',
	                'passportimage' => 'required'*/
	            ])->setAttributeNames([
	                'fullname' => 'Full Name',
	                'email' => 'Email',
	                'tel' => 'Phone',
	                'wallet_btc' => 'Wallet BTC',
	                'country' => 'Country',
	                'address' => 'Address',
	                /*'portraitimage' => 'Portrait image',
	                'passportimage' => 'Passport image'*/
	            ])->validate();
	            // check unique email
	            if(Customer::where('email', $request->email)->where('email', '<>', $request->email)->first()) {
	            	return redirect()->route('backend.customer.vEdit')->withErrors(['email' => 'Email already exists!']);
	            }
	            // check unique tel
	            if(Customer::where('tel', $request->tel)->where('tel', '<>', $request->tel)->first()) {
	            	return redirect()->route('backend.customer.vEdit')->withErrors(['tel' => 'Tel already exists!']);
	            }
	            // check unique wallet_btc
	            if(Customer::where('wallet_btc', $request->wallet_btc)->where('wallet_btc', '<>', $request->wallet_btc)->first()) {
	            	return redirect()->route('backend.customer.vEdit')->withErrors(['wallet_btc' => 'Wallet BTC already exists!']);
	            }
	            $customer->fullname = $request->fullname;
	            $customer->email = $request->email;
	            $customer->tel = $request->tel;
	            $customer->wallet_btc = $request->wallet_btc;
	            $customer->dob = $request->dob;
	            $customer->sex = $request->sex;
	            $customer->country = $request->country;
	            $customer->address = $request->address;
	            $customer->status = Customer::STATUS_ACTIVE;
	            $customer->password = bcrypt($request->password);
	            /*upload file to public/upload/profile*/
	            if ($request->hasFile('portraitimage')) {
	                $path = $request->file('portraitimage')->store(
	                    'profile/'.$request->email.date("Y",time()).'/'.date("m",time()).'/'.date("d",time()), 'upload'
	                );
	                $customer->portraitimage = 'upload/'.$path;
	            }
	            /*upload file to public/upload/profile*/
	            if ($request->hasFile('passportimage')) {
	                $path = $request->file('passportimage')->store(
	                    'profile/'.$request->email.date("Y",time()).'/'.date("m",time()).'/'.date("d",time()), 'upload'
	                );
	                $customer->passportimage = 'upload/'.$path;
	            }
	            $customer->save();
	            \Session::flash( 'msg_game', __( 'messages.edit_success' ) );
				return redirect()->route( 'backend.customer.index' );
	        }
	        $data['model'] = $customer;
	        $data['listGender'] = Customer::$SEX;
	        $data['listCountry'] = Customer::$COUNTRY;
	        return view('backend.customer.edit', $data);
	    } else {
            return redirect()->route('backend.site.error', ['errorCode'=>404, 'msg'=>"Not found request!"]);
        }
	}

	public function resetPass($id) {
		$customer = Customer::findOrFail($id);
		$customer->password = \Hash::make('123456');
		$customer->save();
		\Session::flash( 'msg_game', __( 'messages.edit_success' ) );
		return redirect()->route( 'backend.customer.index' );
	}
}
