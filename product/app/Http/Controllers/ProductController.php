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
        $products = json_decode(file_get_contents('http://localhost/Product/src/testJSON.json'), true);
      //  ($products);
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
        return json_encode([
            'averageRating' => $this->averageRating,
            'images' => $this->images,
            'meetingPoint' => $this->meetingPoint,
        ]);
    }

    private function parseProduct(array $product)
    {
        $averageRating = 0; 
        $averageCount = 0;  
        $reviewCountTotals = $product["reviews"]["reviewCountTotals"];
        
        foreach ( $reviewCountTotals as $item) {            
            $averageRating += $item["rating"] * $item["count"];
        }        
       
        $averageRating = round($averageRating / $product["reviews"]["totalReviews"], 2);

        $images = []; $aux = 0;  $i=0;                                 // dd($image['variants'][$i]);
        foreach ($product['images'] as $image) {            
            $val = ($image['variants'][$i]["height"]*$image['variants'][$i]["width"]);
            if ($aux < $val) {
                $images = [$image['variants'][$i]["url"]];
                $aux = $val;
            }  
            $i++;  
            //$variants = $image['variants'];
        }
      
        //dd($variants);
        $meetingPoint = '';
        $description = $product['description'];
        if (strpos($description, 'Meeting point:') === 0) {
            $meetingPoint = substr($description, 13);
           // $meetingPoint = substr('\n',"");
            $meetingPoint = ucfirst($meetingPoint);
        }

        $product = new ProductController();
        $product->setAverageRating($averageRating);
        $product->setImages($images);
        $product->setMeetingPoint($meetingPoint);
        dd($product->toJSON());
        return $product->toJSON();
    }
}
