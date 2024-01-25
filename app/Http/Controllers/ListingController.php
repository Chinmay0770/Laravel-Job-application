<?php

namespace App\Http\Controllers;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class ListingController extends Controller
{
    // show all listings
    public function index(){
        return view('listings.index', [
            'heading' => 'Listings',
            'listings' => Listing::latest()->filter(request(['tag','search']))->paginate(4)
        ]);
    }

    // show individual listing
    public function show($id){
        $listing = Listing::find($id);
        if ($listing){
            return view('listings.show',[
                'heading' => 'Listings',
                'listing' => Listing::find($id)
            ]);
        }
        else{
            abort('404');
        }
    }

    // show create form
    public function create(){
        return view('listings.create');
    }

    //store listing data
    public function store(Request $request){

        // dd($request);
        $formfields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings','company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if($request->hasFile('logo')) {
            $formfields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $formfields['user_id'] = auth()->id();

        Listing::create($formfields);

        return redirect('/')->with('message', 'Job posted successfully');
    }

    // show edit form
    public function edit($listing){
        $listing = Listing::find($listing);
        return view('listings.edit', ['listing' => $listing]);
    }

    public function update(Request $request, $listing) {

        if($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized Action');
        }

        $listing = Listing::find($listing);
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $listing->update($formFields);

        return back()->with('message', 'Listing updated successfully!');
    }

    // delete function
    public function delete($listing){
        $listing = Listing::find($listing);
        if($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized Action');
        }
        $listing->delete();
        return redirect('/')->with('message','Listing Deleted Successfully');
    }

    // manage listings
    public function manage(){
        return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);
    }
}
