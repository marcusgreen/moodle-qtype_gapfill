==New question type template README file==

Welcome to the new question type template.

This blank skeleton is a good place to start if you want to implement your own Moodle question type
plugin.

Another good way to start is by looking at an existing question type that has good documentation in
the code like TODO (if there is one, otherwise we'll hope that Tims will "light the way" :-) )

The latest version of the template can be found in CVS in
http://moodle.cvs.sourceforge.net/moodle/contrib/plugins/question/type/TEMPLATE/. Because you want
your own copy do a cvs export, not a cvs checkout. The package can also been downloaded from
[http://moodle.org/mod/data/view.php?d=13&rid=443 the Modules and plugins database].

The latest version of this help can be read (nicely formatted) at
http://docs.moodle.org/en/How_to_write_a_question_type_plugin

'''WARNING, THIS TEMPLATE IS NOT COMPLETE YET!'''

==Getting started==

Before you get to the interesting bit, you need to do a bit of file-renaming and
search-and-replacing to turn this generic template into your own question type. You need to have
chosen two things 

;The identifier for your question type
:This is a string of lowercase letters and perhaps underscores that the Moodle code  uses to refer
to your question type. This needs to be unique, so perhaps start it with your initials. For example,
all the quetions types I create while working at the OU will be referred to as ou_something. For the
rest of these instructions, I will assume you have chosen 'myqtypeidentifier'.
;The name of your question type
:This is the name that people will see in the Moodle User-interface (in the English translation).
For these instructions I will assume you have chosen  'My Question Type Name'.

Then you need to

# Change all the places TEMPLATE appears in file names to myqtypeidentifier. That is:
#* rename the top level TEMPLATE directory to myqtypeidentifier.
#* rename the language file lang/en_utf8/qtype_TEMPLATE.php to lang/en_utf8/qtype_myqtypeidentifier.php
#* rename the help file lang/en_utf8/help/TEMPLATE/TEMPLATE.html to 
   lang/en_utf8/help/myqtypeidentifier/myqtypeidentifier.html
#* rename the editing form edit_TEMPLATE_form.php to edit_myqtypeidentifier_form.php.
# Search and replace 'QTYPEID' with 'myqtypeidentifier' in all the files. You should find:
#* 2 occurrences in the language file
#* 1 occurrence in db/install.xml
#* 5 occurrences in simpletest/testquestiontype.php
#* 2 occurrences in edit_myqtypeidentifier_form.php
#* 3 occurrences in questiontype.php
# Search and replace 'QTYPENAME' with 'My Question Type Name' in all the files. You should find:
#* 1 occurrence in the help file
#* 3 occurrences in the language file
#* 1 occurrence in edit_myqtypeidentifier_form.php
#* 2 occurrences in questiontype.php
# Search and replace YOURNAME with your name. (This is only in the comments at  the top of each file
  so it is not critical, but surely you want to take credit for your work.) You should find one occurrence in:
#* tlang/en_utf8/qtype_myqtypeidentifier.php
#* simpletest/testquestiontype.php
#* edit_myqtypeidentifier_form.php
#* questiontype.php
# Search and replace YOUREMAILADDRESS with your email address. (Again, this is only in the file
  header comments, but it is helpful if people can contact you if they have any questions about your code.)
#*  There should be one occurrence of YOUREMAILADDRESS in each of the files listed under YOURNAME.
# Search and replace YOURPACKAGENAME with a package name for your code. This is used by PHPdocumentor
  when building the documentation for your classes. I suggest you make up one package name for all
  the question types you write. For example all the question types I write are in the package ou_questiontypes.
#* There should be one occurrence of YOURPACKAGENAME in each of the files listed under YOURNAME.
# Edit icon.gif to make an icon that represents your question type.
# Move your new question type folder in the question/type folder of your Moodle development codebase,
  so you can test the code as you work on it.

==Now for the interesting bit==

Now you need to write the code to

# Create any database tables you need. This works just like normal in Moodle with the files in the
  db directory and the version.php file. If you don't need to create any database tables, you can
  delete the db directory.
# Create the editing form for you question type, and the code to populate it.  That means adding
  code to the files editquestion.html and editquestion.php.
# Create the template that will display the question to the student. This is in display.html. To
  make good flexible formating you should use CSS classes and use already existing ones when
  possible. If you need custom CSS for you question type, you can put it in the styles.css file.
# Implement the rest of the question type class. This is in questiontype.php. You need to 
## TODO finish writing this section.
# If you question type needs any JavaScript, you can put it in the script.js file.
# Make sure that the language file contains all the strings you refer to. Note that some of the
  strings you want may already be in the question.php and moodle.php language files in Moodle
  core. If so, use the existing strings.
# Write the help file for your question type, and any other help files you need.
# Write a set of unit tests for you question type.

All the places in the code where you need to do things are marked TODO, and there are comments
right there giving more detailed instructions.

''Note that these comments are not complete or accurate yet. I have only just started implementing
my own question type, and I will be filling in those comments and finishing this document as I
go along.''~~~~

==When you have finished==

Consider checking your question type into the contrib area of the Moodle CVS server (if you have
access, if not, request it or post your code as a zip file in the quiz forum) so that other people
can share it.

Add your new question type to the [http://moodle.org/mod/data/view.php?id=6009 modules and plugins
database].
