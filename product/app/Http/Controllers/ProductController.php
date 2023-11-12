<?php

namespace App\Http\Controllers;

use App\Models\products;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $averageRating = 0.0;
    private $images = [];
    private $meetingPoint = "";
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = json_decode(file_get_contents('https://jsoneditoronline.org/#left=cloud.baf82d0b0d6946f8a6b006fafb2a9293'), true);
        dd($products);
        if ($products) {
        
        foreach ($products['products'] as $product) {
            if ($product['status'] === 'ACTIVE') {
                $product = $this->parseProduct($product);

                echo json_encode($product, JSON_PRETTY_PRINT);
            }
        }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(request $request)
    {
        $products = products::create($request->all());
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $products = products::get($request); // verificar
        $products->fill($request->all());
        $this->validate($request, [
            "name"=> "required|string|max:1000"
        ]);
        $products->save();
        return response()->json($products);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $products = products::get( $id );
        return response()->json($products);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function setAverageRating(float $averageRating)
    {
        $this->averageRating = $averageRating;
    }

    public function getAverageRating()
    {
        return $this->averageRating;
    }

    public function setImages(array $images)
    {
        $this->images = $images;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function setMeetingPoint(string $meetingPoint)
    {
        $this->meetingPoint = $meetingPoint;
    }

    public function getMeetingPoint()
    {
        return $this->meetingPoint;
    }

    public function toJSON()
    {
        return [
            'averageRating' => $this->averageRating,
            'images' => $this->images,
            'meetingPoint' => $this->meetingPoint,
        ];
    }

    private function parseProduct(array $product)
    {
        $averageRating = 0;
        $reviewCountTotals = $product['reviews']['reviewCountTotals'];

        for ($i = 1; $i <= 5; $i++) {
            $averageRating += $reviewCountTotals[$i] * $i;
        }

        $averageRating = round($averageRating / array_sum($reviewCountTotals), 2);

        $images = [];
        foreach ($product['images'] as $image) {
            $variants = $image['variants'];
            $maxWidth = max(array_column($variants, 'width'));
            $images[] = $variants[$maxWidth]['url'];
        }

        $meetingPoint = '';
        $description = $product['description'];
        if (strpos($description, 'Meeting point:') === 0) {
            $meetingPoint = substr($description, 13);
            $meetingPoint = ucfirst($meetingPoint);
        }

        $product = new products();
        $product->setAverageRating($averageRating);
        $product->setImages($images);
        $product->setMeetingPoint($meetingPoint);

        return $product;
    }
}
