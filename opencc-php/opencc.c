/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2008 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author: BYVoid <byvoid.kcp@gmail.com>                                |
  +----------------------------------------------------------------------+
*/

/* $Id: header 252479 2008-02-07 19:39:50Z iliaa $ */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_opencc.h"

ZEND_DECLARE_MODULE_GLOBALS(opencc)

/* True global resources - no need for thread safety here */
static int le_opencc;

/* Function Entry */
const zend_function_entry opencc_functions[] = {
	PHP_FE(opencc_open,	NULL)
	PHP_FE(opencc_close,	NULL)
	PHP_FE(opencc_convert,	NULL)
	PHP_FE(opencc_set_conversion_mode,	NULL)
	{NULL, NULL, NULL}
};

/* Module Entry */
zend_module_entry opencc_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
	STANDARD_MODULE_HEADER,
#endif
	"opencc",
	opencc_functions,
	PHP_MINIT(opencc),
	PHP_MSHUTDOWN(opencc),
	PHP_RINIT(opencc),
	PHP_RSHUTDOWN(opencc),
	PHP_MINFO(opencc),
#if ZEND_MODULE_API_NO >= 20010901
	PACKAGE_VERSION,
#endif
	STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_OPENCC
ZEND_GET_MODULE(opencc)
#endif

PHP_INI_BEGIN()
    //STD_PHP_INI_ENTRY("opencc.global_value",      "42", PHP_INI_ALL, OnUpdateLong, global_value, zend_opencc_globals, opencc_globals)
    STD_PHP_INI_ENTRY("opencc.default_config", "zhs2zht.ini", PHP_INI_ALL, OnUpdateString, default_config, zend_opencc_globals, opencc_globals)
PHP_INI_END()

static void php_opencc_init_globals(zend_opencc_globals *opencc_globals)
{
	opencc_globals->default_config = NULL;
}

/* Module Initialize */
PHP_MINIT_FUNCTION(opencc)
{
	REGISTER_INI_ENTRIES();
	return SUCCESS;
}

/* Module Shotdown */
PHP_MSHUTDOWN_FUNCTION(opencc)
{
	UNREGISTER_INI_ENTRIES();
	return SUCCESS;
}

PHP_MINFO_FUNCTION(opencc)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "opencc support", "enabled");
	php_info_print_table_end();

	DISPLAY_INI_ENTRIES();
}

/* Initialize */
PHP_RINIT_FUNCTION(opencc)
{
	return SUCCESS;
}

/* Module Shutdown */
PHP_RSHUTDOWN_FUNCTION(opencc)
{
	return SUCCESS;
}

PHP_FUNCTION(opencc_open)
{
	opencc_t od;
	char * config = NULL;
	int config_len;
	
	if (ZEND_NUM_ARGS() > 0)
	{
		if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &config, &config_len) == FAILURE)
		{
			RETURN_BOOL(0);
		}
	}
	
	if (config == NULL || *config == 0)
	{
		config = OPENCC_G(default_config);
	}

	od = opencc_open(config);
	if (od == (opencc_t) -1)
	{
		RETURN_BOOL(0);
	}
	
	RETURN_RESOURCE((long) od);
}

PHP_FUNCTION(opencc_close)
{
	zval * zod;
	opencc_t od;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &zod) == FAILURE)
	{
		RETURN_BOOL(0);
	}
	
	od = (opencc_t) zod->value.lval;
	if (od == (opencc_t) -1 || od == NULL)
	{
		RETURN_BOOL(0);
	}
	
	if (opencc_close(od) == -1)
	{
		RETURN_BOOL(0);
	}
	else
	{
		RETURN_BOOL(1);
	}
}

PHP_FUNCTION(opencc_convert)
{
	zval * zod;
	char * inbuf = NULL;
	int inbuf_len;
	opencc_t od;
	
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs", &zod, &inbuf, &inbuf_len) == FAILURE)
	{
		RETURN_BOOL(0);
	}
	
	od = (opencc_t) zod->value.lval;
	if (od == (opencc_t) -1 || od == NULL)
	{
		RETURN_BOOL(0);
	}
	
	if (inbuf_len == 0)
	{
		char * rs = emalloc(sizeof(char));
		*rs = '\0';
		RETURN_STRINGL(rs, 0, 0);
	}
	
	char * outbuf = opencc_convert_utf8(od, inbuf, inbuf_len);
	int len = strlen(outbuf);
	
	char * rs = emalloc(sizeof(char) * (len + 1));
	strncpy(rs, outbuf, len + 1);
	free(outbuf);
	
	RETURN_STRINGL(rs, len, 0);
}

PHP_FUNCTION(opencc_set_conversion_mode)
{
	zval * zod;
	opencc_t od;
	long conversion_mode;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rl", &zod, &conversion_mode) == FAILURE)
	{
		RETURN_BOOL(0);
	}

	od = (opencc_t) zod->value.lval;

	if (od == (opencc_t) -1 || od == NULL)
	{
		RETURN_BOOL(0);
	}

	opencc_set_conversion_mode(od, conversion_mode);

	RETURN_BOOL(1);
}
