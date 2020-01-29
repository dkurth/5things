# Five Things

The 5 Things Generator is intended to facilitate rehearsing the game of 5 Things by automatically generating an active activity and three substitutions

## Features
The 5 Things Generator contains the following features:
<ul>
    <li>Random suggestions of active activities and associated substitutions pulled from a user-generated database</li>
    <li>Substituted items drawn from a dictionary of nouns</li>
    <li>Interface for users to add activities and substitutes to the database*</li>
    <li>Text recognition to help minimize duplicated entries in the database</li>
    <li>A searchable text document showing the contents of the database</li>
</ul>

This script was developed independently by a CSz player (who came up with the idea) and a software programmer (who did all the actual work). No endorsement from CSz or any other organization has been given or implied.

*When entering suggestions, please enter only suggestions that would be accepted in a family-friendly match with the Brown-Bag foul in full effect.

## Future plans

Some of our planned enhancements are:


<ul>
    <li>An interface for editing the replacement nouns (you can only edit activities currently).</li>
    <li>Ability for users to specify that items be replaced with person, animal, place, and/or thing</li>
</ul>

## Notes for the technically minded

If you would like to run this code locally, here are the steps to get set up:

- fork or clone to get a copy of the code (e.g., run `git clone https://github.com/dkurth/5things`)
- download a copy of the live database (from http://5things.dx3x.com/5things.db3).  This can change at any time and is not part of the repository.
- put this db3 file in the 5things/public directory.
- from the 5things directory, run `composer install` to install dependencies.  (This step requires [Composer](https://getcomposer.org/doc/00-intro.md))
- this next step is lame, and I apologize, but I haven't figured out a fix yet.  There is a directory called 5things/public/activity that we use in production to have an .htaccess file protecting the link to edit the database.  It needs to be there in production, but it breaks database editing locally.  So, I normally delete or rename it when working on this locally, and just don't commit that change.
- `cd` into the `public` directory, then run this: `php -S localhost:8000` (or use a different port if you prefer)
- open your browser to http://localhost:8000

The code uses the Slim framework, a lightweight PHP framework that's pretty straightforward.  Files you might want are:
- views, all in the `templates` directory
- code to talk to the database, in `src/FiveThings/ActivityLoader.php`
- URL routing definitions, in `src/routes.php`

For example, if you want to contribute by creating a page to edit the list of replacement nouns, you could follow this structure:
- Create a ReplacementItemLoader that extends the Loader class, just like ActivityLoader.
- Create a view in templates/item_edit.phtml to display a textarea with all the items, which a user can edit and save.
- Add a route for "/item/edit" that calls ReplacementItemLoader::LoadItems(), then passes them to item_edit.phtml to render.
- Add code in routes.php to call ReplacementItemLoader::Save() to save the content of the textarea when the user clicks a save button.  I imagine this would wipe out the ReplacementItem table and re-create it with the content of the textarea, but there are other ways you could do it.


