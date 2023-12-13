<?php

namespace App\Common\Traits\Items;

trait FormValidationMetadataAware
{
	/**
	 * Returns validation metadata for item draft edit form.
	 *
	 * @return array
	 */
	public function getDraftValidationMetadata()
	{
		return [
			'*'                => [
				'required' => ['enabled' => false, 'rules' => ['required']],
			],
			'title'            => [
				'min'      => ['enabled' => true, 'rules' => ['minSize[4]']],
				'max '     => ['enabled' => true, 'rules' => ['maxSize[255]']],
				'required' => ['enabled' => true, 'rules' => ['required']],
			],
			'weight'           => [
				'min'      => ['enabled' => false, 'rules' => ['min[0.001]']],
				'max'      => ['enabled' => false, 'rules' => ['max[999999999999.999]']],
				'required' => ['enabled' => false, 'rules' => ['required']],
			],
			'quantity'         => [
				'min'      => ['enabled' => false, 'rules' => ['min[1]']],
				'required' => ['enabled' => false, 'rules' => ['required']],
			],
			'year'             => [
				'min'      => ['enabled' => false, 'rules' => ['min[1000]']],
				'required' => ['enabled' => false, 'rules' => ['required']],
			],
			'price'            => [
				'min'      => ['enabled' => false, 'rules' => ['min[1]']],
				'required' => ['enabled' => false, 'rules' => ['required']],
			],
			'min_sale_quantity' => [
				'min'      => ['enabled' => false, 'rules' => ['min[1]']],
				'required' => ['enabled' => false, 'rules' => ['required']],
			],
			'max_sale_quantity' => [
				'min'      => ['enabled' => false, 'rules' => ['min[1]']],
				'required' => ['enabled' => false, 'rules' => ['required']],
			],
			'purchase_options' => [
				'required' => ['enabled' => false, 'rules' => ['minCheckbox[1]']],
			],
		];
	}

	/**
	 * Returns validation metadata for item edit form.
	 *
	 * @return array
	 */
	public function getItemValidationMetadata()
	{
		return [
			'*'                => [
				'required' => ['enabled' => true, 'rules' => ['required']],
			],
			'title'            => [
				'max'      => ['enabled' => true, 'rules' => ['maxSize[70]']],
				'min'      => ['enabled' => true, 'rules' => ['minSize[4]']],
				'required' => ['enabled' => true, 'rules' => ['required']],
			],
			'size'            => [
				'min'      => ['enabled' => true, 'rules' => ['min[0.01]']],
				'max'      => ['enabled' => true, 'rules' => ['max[99999.99]']],
				'required' => ['enabled' => true, 'rules' => ['required']],
			],
			'weight'           => [
				'min'      => ['enabled' => true, 'rules' => ['min[0.001]']],
				'max'      => ['enabled' => true, 'rules' => ['max[999999999999.999]']],
				'required' => ['enabled' => true, 'rules' => ['required']],
			],
			'quantity'         => [
				'min'      => ['enabled' => true, 'rules' => ['min[1]']],
				'max'      => ['enabled' => true, 'rules' => ['max[1000000000]']],
				'required' => ['enabled' => true, 'rules' => ['required']],
			],
			'year'             => [
				'min'      => ['enabled' => true, 'rules' => ['min[1000]']],
				'required' => ['enabled' => true, 'rules' => ['required']],
			],
			'price'            => [
				'min'      => ['enabled' => true, 'rules' => ['min[0.01]']],
				'max'      => ['enabled' => true, 'rules' => ['max[9999999.99]']],
				'required' => ['enabled' => true, 'rules' => ['required']],
			],
			'final_price'      => [
				'min'      => ['enabled' => true, 'rules' => ['min[0]']],
				'max'      => ['enabled' => true, 'rules' => ['max[9999999.99]']],
				'required' => ['enabled' => true, 'rules' => ['required']],
			],
			'variant_price'    => [
				'min'      => ['enabled' => true, 'rules' => ['min[0.01]']],
				'max'      => ['enabled' => true, 'rules' => ['max[9999999.99]']],
			],
			'min_sale_quantity' => [
				'min'      => ['enabled' => true, 'rules' => ['min[1]']],
				'required' => ['enabled' => true, 'rules' => ['required']],
			],
			'max_sale_quantity' => [
				'min'      => ['enabled' => true, 'rules' => ['min[1]']],
				'required' => ['enabled' => true, 'rules' => ['required']],
			],
			'out_of_stock_quantity' => [
				'min'      => ['enabled' => true, 'rules' => ['min[1]']],
			],
			'purchase_options' => [
				'required' => ['enabled' => true, 'rules' => ['minCheckbox[1]']],
			],
		];
	}
}
