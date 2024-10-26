{assign var='profileId' value=$block.profile_id}
{include file="CRM/Contactlayout/Page/Inline/ProfileBlock.tpl" profileBlock=$profileBlocks.$profileId relatedContact=$block.rel_cid}
