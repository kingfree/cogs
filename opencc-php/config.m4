#AC_DEFINE(PACKAGE_VERSION, "0.2.0")
#AC_DEFINE(PACKAGE_NAME, "opencc")
#AC_DEFINE(PACKAGE_BUGREPORT, "http://code.google.com/p/opencc/issues/entry")

PHP_ARG_ENABLE(opencc, whether to enable opencc support, 
[  --enable-opencc           Enable opencc support])

if test "$PHP_OPENCC" != "no"; then

  SEARCH_PATH="/usr/local /usr"
  SEARCH_FOR="/include/opencc/opencc.h"
  if test -r $PHP_OPENCC/$SEARCH_FOR; then
    OPENCC_DIR=$PHP_OPENCC
  else # search default path list
  
    AC_MSG_CHECKING([for opencc files in default path])
    
    for i in $SEARCH_PATH ; do
      if test -r $i/$SEARCH_FOR; then
        OPENCC_DIR=$i
        AC_MSG_RESULT(found in $i)
      fi
    done
    
  fi
  
  if test -z "$OPENCC_DIR"; then
    AC_MSG_RESULT([not found])
    AC_MSG_ERROR([Please reinstall the opencc distribution])
  fi

  PHP_ADD_INCLUDE($OPENCC_DIR/include/opencc)
  PHP_ADD_LIBRARY_WITH_PATH(opencc, $OPENCC_DIR/lib, OPENCC_SHARED_LIBADD)
  
  PHP_SUBST(OPENCC_SHARED_LIBADD)

  PHP_NEW_EXTENSION(opencc, opencc.c, $ext_shared)
fi
