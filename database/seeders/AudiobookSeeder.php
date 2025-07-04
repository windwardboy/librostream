<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Audiobook; // Import the Audiobook model
use App\Models\Category;  // Import the Category model

class AudiobookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find a category to associate with the audiobooks
        $fictionCategory = Category::where('name', 'Fiction')->first();
        $scifiCategory = Category::where('name', 'Science Fiction')->first();

        // if ($fictionCategory) {
        //     Audiobook::create([
        //         'title' => 'The Adventures of Sherlock Holmes',
        //         'author' => 'Arthur Conan Doyle',
        //         'narrator' => 'John Smith', // Example narrator
        //         'description' => 'A collection of twelve short stories by Arthur Conan Doyle, featuring his fictional detective Sherlock Holmes.',
        //         'cover_image' => 'https://www.gutenberg.org/cache/epub/1661/pg1661.cover.medium.jpg', // Example cover
        //         'duration' => '10h 30m',
        //         'source_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3', // Sample MP3 URL
        //         'category_id' => $fictionCategory->id,
        //     ]);
        // }

        // if ($scifiCategory) {
        //     Audiobook::create([
        //         'title' => 'The War of the Worlds',
        //         'author' => 'H. G. Wells',
        //         'narrator' => 'Jane Doe', // Example narrator
        //         'description' => 'An early science fiction novel which describes an invasion of England by aliens from Mars.',
        //         'cover_image' => 'https://www.gutenberg.org/cache/epub/36/pg36.cover.medium.jpg', // Example cover
        //         'duration' => '7h 15m',
        //         'source_url' => 'https://www.learningcontainer.com/wp-content/uploads/2020/02/Kalimba.mp3', // Sample MP3 URL
        //         'category_id' => $scifiCategory->id,
        //     ]);
        // }

        // Add more sample audiobooks as needed
    }
}
