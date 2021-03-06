<?php
namespace Dust\Helper;

use Dust\Evaluate;

class ContextDump
{
    public function __invoke(Evaluate\Chunk $chunk, Evaluate\Context $context, Evaluate\Bodies $bodies, Evaluate\Parameters $params) {
        //get config
        $current = !isset($params->{'key'}) || $params->{'key'} != 'full';
        $output = !isset($params->{'to'}) || $params->{'to'} != 'console';
        //ok, basically we're gonna give parent object w/ two extra values, "__forcedParent__", "__child__", and "__params__"
        $getContext = function (Evaluate\Context $ctx) use ($current, &$getContext)
        {
            //first parent
            $parent = !$current && $ctx->parent != NULL ? $getContext($ctx->parent) : (object) [];
            //now start adding pieces
            $parent->__child__ = $ctx->head == NULL ? NULL : $ctx->head->value;
            if($ctx->head != NULL && $ctx->head->forcedParent !== NULL)
            {
                $parent->__forcedParent__ = $ctx->head->forcedParent;
            }
            if($ctx->head != NULL && !empty($ctx->head->params))
            {
                $parent->__params__ = $ctx->head->params;
            }

            return $parent;
        };
        //now json_encode
        $str = $context->parent == NULL ? '{ }' : json_encode($getContext($context->parent), JSON_PRETTY_PRINT);
        //now put where necessary
        if($output)
        {
            return $chunk->write($str);
        }
        echo($str . "\n");

        return $chunk;
    }

}