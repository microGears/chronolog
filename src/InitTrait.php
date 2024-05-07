<?php
/**
 * @author    Maxim Kirichenko
 * @copyright Copyright (c) 2009-2017 Maxim Kirichenko (kirichenko.maxim@gmail.com)
 * @license   GNU General Public License v3.0 or later
 */

namespace Chronolog;


trait InitTrait
{
    public function initialize(array $config = [], $context = null)
    {
        if ($context == null || !is_object( $context )) {
            $context =& $this;
        }

        if (count( $config )) {
            foreach ($config as $key => $val) {
                if (is_numeric( $key )) {
                    continue;
                }

                if (method_exists( $context, $method = 'set'.Utilits::normalizeName( $key ) )) {
                    call_user_func( [$context, $method], $val );
                }
                else {
                    
                    if(strpos( phpversion(), '8.2' ) === 0 && !property_exists($context, $key)){
                        throw new \Exception( sprintf( 'Class  "%s" does not have the "%s" property, dynamic creation of properties is not supported since version 8.2', Utilits::className($context,false), $key ) );
                    }

                    $context->{$key} = $val;
                }
            }
        }
    }
} 