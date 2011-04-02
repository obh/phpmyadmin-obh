#!/bin/sh
#
# vim: expandtab sw=4 ts=4 sts=4:
#

# More documentation about making a release is available at:
# http://wiki.phpmyadmin.net/pma/Releasing

# Fail on undefined variables
set -u
# Fail on failure
set -e

KITS="all-languages english"
COMPRESSIONS="zip-7z tbz txz tgz 7z"

if [ $# -lt 2 ]
then
  echo "Usages:"
  echo "  create-release.sh <version> <from_branch> [--tag]"
  echo ""
  echo "If --tag is specified, relase tag is automatically created"
  echo ""
  echo "Examples:"
  echo "  create-release.sh 2.9.0-rc1 QA_2_9"
  echo "  create-release.sh 2.9.0 MAINT_2_9_0 --tag"
  exit 65
fi


# Checks whether remote branch has local tracking branch
ensure_local_branch() {
    if ! git branch | grep -q '^..'"$1"'$' ; then
        git branch --track $1 origin/$1
    fi
}

# Marks current head of given branch as head of other branch
# Used for STABLE/TESTING tracking
mark_as_release() {
    branch=$1
    rel_branch=$2
    echo "* Marking release as $rel_branch"
    ensure_local_branch $rel_branch
    git checkout $rel_branch
    git merge -s recursive -X theirs $branch
}

# Read required parameters
version=$1
shift
branch=$1
shift

cat <<END

Please ensure you have incremented rc count or version in the repository :
     - in libraries/Config.class.php PMA_Config::__constructor() the line
          " \$this->set( 'PMA_VERSION', '$version' ); "
     - in Documentation.html the 2 lines
          " <title>phpMyAdmin $version - Documentation</title> "
          " <h1>phpMyAdmin $version Documentation</h1> "
     - in README

Continue (y/n)?
END
read do_release

if [ "$do_release" != 'y' ]; then
    exit 100
fi

# Ensure we have tracking branch
ensure_local_branch $branch

# Create working copy
mkdir -p release
workdir=release/phpMyAdmin-$version
if [ -d $workdir ] ; then
    echo "Working directory '$workdir' already exists, please move it out of way"
    exit 1
fi
git clone --local . $workdir
cd $workdir

# Checkout branch
ensure_local_branch $branch
git checkout $branch

# Check release version
if ! grep -q "'PMA_VERSION', '$version'" libraries/Config.class.php ; then
    echo "There seems to be wrong version in libraries/Config.class.php!"
    exit 2
fi
if ! grep -q "phpMyAdmin $version - Documentation" Documentation.html ; then
    echo "There seems to be wrong version in Documentation.html"
    exit 2
fi
if ! grep -q "Version $version\$" README ; then
    echo "There seems to be wrong version in README"
    exit 2
fi

# Cleanup release dir
LC_ALL=C date -u > RELEASE-DATE-${version}

# Building Documentation.txt
echo "* Generating Documentation.txt"
LC_ALL=C w3m -dump Documentation.html > Documentation.txt

# Check for gettext support
if [ -d po ] ; then
    GETTEXT=1
else
    GETTEXT=0
fi

# Generate mo files
if [ $GETTEXT -eq 1 ] ; then
    echo "* Generating mo files"
    ./scripts/generate-mo
    echo "* Removing gettext source files"
    rm -rf po
fi

if [ -f ./scripts/compress-js ] ; then
    echo "* Compressing javascript files"
    ./scripts/compress-js
    rm -rf sources
fi

echo "* Removing unneeded files"

# Remove test directory from package to avoid Path disclosure messages
# if someone runs /test/wui.php and there are test failures
rm -rf test

# Remove javascript compiler, no need to ship it
rm -rf scripts/google-javascript-compiler/

# Remove scripts which are not useful for user
for s in compress-js create-release.sh generate-mo mergepo.py php2gettext.sh remove_control_m.sh update-po upload-release pending-po pendingpo.py ; do
    rm -f scripts/$s
done

# Remove git metadata
rm -rf .git
find . -name .gitignore -print0 | xargs -0 -r rm -f

cd ..

# Prepare all kits
for kit in $KITS ; do
    # Copy all files
	name=phpMyAdmin-$version-$kit
	cp -r phpMyAdmin-$version $name

	# Cleanup translations
    cd phpMyAdmin-$version-$kit
    scripts/lang-cleanup.sh $kit
    rm -f scripts/lang-cleanup.sh
    cd ..

    # Remove tar file possibly left from previous run
    rm -f $name.tar

    # Prepare distributions
    for comp in $COMPRESSIONS ; do
        case $comp in
            tbz|tgz|txz)
                if [ ! -f $name.tar ] ; then
                    echo "* Creating $name.tar"
                    tar cf $name.tar $name
                fi
                if [ $comp = tbz ] ; then
                    echo "* Creating $name.tar.bz2"
                    bzip2 -9k $name.tar
                fi
                if [ $comp = txz ] ; then
                    echo "* Creating $name.tar.xz"
                    xz -9k $name.tar
                fi
                if [ $comp = tgz ] ; then
                    echo "* Creating $name.tar.gz"
                    gzip -9c $name.tar > $name.tar.gz
                fi
                ;;
            zip)
                echo "* Creating $name.zip"
                zip -q -9 -r $name.zip $name
                ;;
            zip-7z)
                echo "* Creating $name.zip"
                7za a -bd -tzip $name.zip $name > /dev/null
                ;;
            7z)
                echo "* Creating $name.7z"
                7za a -bd $name.7z $name > /dev/null
                ;;
            *)
                echo "WARNING: ignoring compression '$comp', not known!"
                ;;
        esac

        # Cleanup
        rm -f $name.tar
    done

    # Remove directory with current dist set
    rm -rf $name
done

# Cleanup
rm -rf phpMyAdmin-${version}


echo ""
echo ""
echo ""
echo "Files:"
echo "------"

ls -la *.gz *.zip *.bz2 *.7z

cd ..


if [ $# -gt 0 ] ; then
    echo
    echo "Additional tasks:"
    while [ $# -gt 0 ] ; do
        param=$1
        case $1 in
            --tag)
                tagname=RELEASE_`echo $version | tr . _ | tr '[:lower:]' '[:upper:]' | tr -d -`
                echo "* Tagging release as $tagname"
                git tag -a -m "Released $version" $tagname $branch
                if echo $version | grep -q '^2\.11\.' ; then
                    echo '* 2.11 branch, no STABLE/TESTING update'
                else
                    if echo $version | grep '[a-z_-]' ; then
                        mark_as_release $branch TESTING
                    else
                        # We update both branches here
                        # As it does not make sense to have older testing than stable
                        if echo $version | grep -q '^3\.3\.' ; then
                            echo '* 3.3 branch, no TESTING update'
                        else
                            mark_as_release $branch TESTING
                        fi
                        mark_as_release $branch STABLE
                    fi
                    git checkout master
                fi
                echo "   Dont forget to push tags using: git push --tags"
                ;;
            *)
                echo "Unknown parameter: $1!"
                exit 1
        esac
        shift
    done
    echo
fi

cat <<END


Todo now:
---------

1. If not already done, tag the repository with the new revision number
   for a plain release or a release candidate:
    version 2.7.0 gets two tags: RELEASE_2_7_0 and STABLE
    version 2.7.1-rc1 gets RELEASE_2_7_1RC1 and TESTING

 2. prepare a release/phpMyAdmin-$version-notes.html explaining in short the goal of
    this release and paste into it the ChangeLog for this release
 3. upload the files to SF, you can use scripts/upload-release, eg.:

        ./scripts/upload-release \$USER $version release
 4. add SF news item to phpMyAdmin project
 5. announce release on freshmeat (http://freshmeat.net/projects/phpmyadmin/)
 6. send a short mail (with list of major changes) to
        phpmyadmin-devel@lists.sourceforge.net
        phpmyadmin-news@lists.sourceforge.net
        phpmyadmin-users@lists.sourceforge.net

    Don't forget to update the Description section in the announcement,
    based on Documentation.html.

 7. increment rc count or version in the repository :
        - in libraries/Config.class.php PMA_Config::__constructor() the line
              " \$this->set( 'PMA_VERSION', '2.7.1-dev' ); "
        - in Documentation.html the 2 lines
              " <title>phpMyAdmin 2.2.2-rc1 - Documentation</title> "
              " <h1>phpMyAdmin 2.2.2-rc1 Documentation</h1> "

 8. add a group for bug tracking this new version, at
    https://sourceforge.net/tracker/admin/index.php?group_id=23067&atid=377408&add_group=1

 9. the end :-)

END
