Gapfill question type V1.5 for Moodle 

A very easy to use Cloze question type that supports drag/drop answers that work on mobile devices. Teachers can define the question with square braces to define the missing words. For example
The [cat] sat on the [mat]. Alternative delimiting characters can be defined during question edit
for example The #cat# sat on the #mat# can be valid.

If the dropdown or dragdrop options are set in the question edit form, it can display 
a shuffled selection  of correct and wrong answers. These can then can be selected via 
dropddropdown lists or javascript powered drag and drop functionality.

This question type was written by Marcus Green

This question type was created and tested under Moodle 2.5, 2.6 and 2.7. 

It will not work with versions of moodle prior to 2.1.

Place the files in a directory 

moodle\question\type\gapfill

Where moodle is webroot for your install.

Version 1.1 includes a count of correct answers and clears incorrect responses in interactive mode
Version 1.2 will colour duplicate answers yellow when discard duplicates mode is used (see help)
Version 1.3 toggle regex for plain string compare. Useful for maths, html and programming language questions
Version 1.4 added support for touch devices such as Apple iOS phones and tablets (iPhone/iPad) and Android devices 
Version 1.5 added support for gaps that are marked correct if left empty using !!, and fixedgapsize
