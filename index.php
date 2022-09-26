<?php

/**********************************************************************************
 * [1] Interface that provides the contract for different readers
 * For example: it can be XML/JSON Remote Endpoint, or CSV/JSON/XML local files
**********************************************************************************/

interface ReaderInterface
{
    // Reading incoming data and parsing it to objects
    public function read(string $input): OfferCollectionInterface;
}

/*********************************************************************************
 * [2] Interface of Data Transfer Objects, that represents external JSON data
**********************************************************************************/

interface OfferInterface {


}

/*********************************************************************************
 * [3] This is an interface for The Collection class that contains Offers
*********************************************************************************/

interface OfferCollectionInterface {
    
    
}

/*********************************************************************************
 * [1.1] Class for the ReaderInterface
*********************************************************************************/

class Reader implements ReaderInterface
{

    // Reading incoming data and parsing it to objects
    public function read(string $input): OfferCollectionInterface
    {
        if ($input != null) {
            $content = file_get_contents($input);
            $json = json_decode($content);
            $result = new OfferCollection($json);
            
            return $result;
        }
        
        return new OfferCollection(null);
    }
}

/*********************************************************************************
 * [2.1] Class for the OfferInterface
*********************************************************************************/

class Offer implements OfferInterface
{
public $offerId;
public $productTitle;
public $vendorId;
public $price;
public $stock;


public function __toString(): string
{
    return "$this->offerId | $this->productTitle | $this->vendorId | $this->price | $this->stock\n";
 }
}

/*********************************************************************************
 * [3.1] Class for the OfferCollectionInterface
*********************************************************************************/

class OfferCollection implements OfferCollectionInterface
{
    // Define new array to store json objects data
    private $offersList = array();
    
    public function __construct($data)
    {
        foreach ($data as $json_object) {
            $offer = new Offer();
            $offer->offerId = $json_object->offerId;
            $offer->productTitle = $json_object->productTitle;
            $offer->vendorId = $json_object->vendorId;
            $offer->price = $json_object->price;
            $offer->stock = $json_object->stock;

            array_push($this->offersList, $offer);
        }
    }
    
    public function get(int $index): OfferInterface
    {
        return $this->offersList[$index];
    }
    
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->offersList);
    }
    
    public function __toString(): string
    {
        return implode("\n", $this->offersList);
    }
}

/*********************************************************************************
 * [4] Creating Logger class to store activity messages
*********************************************************************************/
class Logger {

    private $filename = "logs.txt";
    public function info($message): void {
        $this->log($message, "INFO");
    }
    public function error($message): void {
        $this->log($message, "ERROR");
    }
    
    private function log($message, $type): void {
        $myfile = fopen($this->filename, "a") or die("Unable to open file!");
        $txt = "[$type] $message\n";
        fwrite($myfile, $txt);
        fclose($myfile);
    }
}

/*********************************************************************************
 * [5] Rest of the CLI variables and methods.
*********************************************************************************/


// Json file URL
$json_url = 'data.json';
// Define a new reader
$json_reader = new Reader();
// Getting json objects from data.json file URL
$offers_list = $json_reader->read($json_url);


// Retruning count of offers based on the defined price range.
// This method will return only those offers that are currently in stock
function count_by_price_range($price_from, $price_to){
    global $offers_list;
    $count = 0;
    foreach ($offers_list->getIterator() as $offer) {
        if ($offer->price >= $price_from && $offer->price <= $price_to && $offer->stock != 0) {
            $count++;
        }
    }
    return $count;
}

// Returning count of offers based on the defined vendor id.
// Same like above method this will return only those offers that are curently in stock.
function count_by_vendor_id($vendorId){
    global $offers_list;
    $count = 0;
    foreach ($offers_list->getIterator() as $offer) {
        if ($offer->vendorId == $vendorId && $offer->stock != 0) {
            $count++;
        }
    }
    return $count;
}

// Array of arguments passed to the script
$cli_args = $_SERVER['argv'];
$function_name = $cli_args[1];

// Creating a new Logger
$logger = new Logger();

// Simple switch method returning different functions and logging a message to our new logger
switch ($function_name) {
    case "count_by_price_range": {
        $logger->info("Getting Count By Price Range From: $cli_args[2] TO $cli_args[3]");
        echo count_by_price_range($cli_args[2], $cli_args[3]);
        break;
    }
    case "count_by_vendor_id": {
        $logger->info("Getting Count By vendor Id: $cli_args[2]");
        echo count_by_vendor_id($cli_args[2]);
        break;
    }
}




