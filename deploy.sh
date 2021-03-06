# main config
PLUGINSLUG="woocommerce-my-licences" # returns basename of current directory
CURRENTDIR=`pwd`
MAINFILE="woocommerce-my-licences.php" # this should be the name of your main php file in the wordpress plugin

# git config
GITPATH="$CURRENTDIR" # this file should be in the base of your git repository

# plugin name
PLUGINNAME=`grep "Plugin Name:" "$GITPATH/$MAINFILE" | awk -F' ' '{print $4$5$6$7}' | sed 's/[[:space:]]//g'`

# svn config
SVNPATH="/tmp/$PLUGINSLUG" # path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNURL="http://plugins.svn.wordpress.org/$PLUGINSLUG" # Remote SVN repo on wordpress.org, with no trailing slash
SVNUSER="sebsstudio" # your svn username (case sensitive)

# Let's begin...
echo "....................................................."
echo 
echo "Preparing to deploy $PLUGINNAME to WordPress.org"
echo 
echo "....................................................."

# Check version in readme.txt is the same as plugin file
NEWVERSION1=`grep "^Stable tag:" "$GITPATH/readme.txt" | awk -F' ' '{print $3}' | sed 's/[[:space:]]//g'`
echo "readme.txt version: $NEWVERSION1"
NEWVERSION2=`grep "Version:" "$GITPATH/$MAINFILE" | awk -F' ' '{print $3}' | sed 's/[[:space:]]//g'`
echo "$MAINFILE version: $NEWVERSION2"

if [ "$NEWVERSION1" != "$NEWVERSION2" ]; then echo "Both versions don't match. Exiting...."; exit 1; fi

echo "Versions match in readme.txt and $MAINFILE file. Let's proceed..."

if git show-ref --tags --quiet --verify -- "refs/tags/$NEWVERSION1"
	then
		echo 
		echo "Version $NEWVERSION1 already exists as git tag. Exiting....";
		exit 1;
	else
		echo
		echo "Git version does not exist. Let's proceed..."
fi

cd "$GITPATH"
echo -e "Enter a commit message for this new version: \c"
read COMMITMSG
git commit -am "$COMMITMSG"

echo "Tagging new version in git"
git tag -a "$NEWVERSION1" -m "Tagging version $NEWVERSION1"

echo "Pushing latest commit to origin, with tags"
git push origin master
git push origin master --tags

echo 
echo "Creating local copy of SVN repo ..."
svn co $SVNURL $SVNPATH

echo "Exporting the HEAD of master from git to the trunk of SVN"
git checkout-index -a -f --prefix=$SVNPATH/trunk/

echo "Ignoring github specific files and deployment script"
svn propset svn:ignore "deploy.sh
README.md
CHANGELOG.md
CONTRIBUTING.md
.git
.gitignore" "$SVNPATH/trunk/"

echo "Changing directory to SVN and committing to trunk"
cd $SVNPATH/trunk/
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add
svn commit --username=$SVNUSER -m "$COMMITMSG"

# echo "Creating new SVN tag & committing it"
cd $SVNPATH
svn copy trunk/ tags/$NEWVERSION1/
cd $SVNPATH/tags/$NEWVERSION1
svn commit --username=$SVNUSER -m "Tag $NEWVERSION1"

echo "Removing temporary directory $SVNPATH"
rm -fr $SVNPATH/

echo "*** FINISHED ***"
