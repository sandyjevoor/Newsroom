<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class articlesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
		return[
		'id1'  => $this->id,
		'ext_article_id'  => $this->ext_article_id,
		'title'  => $this->title,
		'headline'  => $this->headline,		
		'kicker'  => $this->kicker,
		'caption'  => $this->caption,
		'tags'  => $this->tags,
		'body'  => $this->body,
		'declaration'  => $this->declaration,
		'location'  => $this->location,
		'language'  => $this->language,
		'district'  => $this->district,		
		'state'  => $this->state,
		'reporter_name'  => $this->reporter_name,
		'reporter_id'  => $this->reporter_id,
		'publish_status'  => $this->publish_status,
		'ingest_status'  => $this->ingest_status,
		'ingest_id'  => $this->ingest_id,
		'created_at'  => $this->created_at,
		'updated_at'  => $this->updated_at,		
		];
    }
}
