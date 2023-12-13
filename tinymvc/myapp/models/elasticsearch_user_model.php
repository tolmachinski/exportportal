<?php

/**
 * @deprecated in favor of \Elasticsearch_Users_Model
 */
class Elasticsearch_User_Model extends TinyMVC_Model
{
    /**
     * @param int $userId
     * @param string $fname
     * @param string $lname
     *
     * @return void
     */
    public function update_other_models(int $userId, string $fname, string $lname): void
    {
        /** @var TinyMVC_Library_Elasticsearch $elasticsearchLibrary */
        $elasticsearchLibrary = library(TinyMVC_Library_Elasticsearch::class);

        //region update questions index
        $elasticsearchLibrary->update_by_query(
            $elasticsearchLibrary->get_term('id_user', $userId),
            "ctx._source['fname']='{$fname}';ctx._source['lname']='{$lname}'",
            'questions'
        );

        $elasticsearchLibrary->update_by_query(
            $elasticsearchLibrary->get_nested(
                'answers',
                [
                    'term'  => [
                        'answers.id_user'   => $userId,
                    ]
                ]
            ),
            <<<SCRIPT
                for(int i = 0; i <= ctx._source['answers'].size() - 1; i++){
                    if (ctx._source.answers[i].id_user == $userId) {
                        ctx._source.answers[i].fname = '$fname';
                        ctx._source.answers[i].lname = '$lname'
                    }
                }
            SCRIPT,
            'questions'
        );
        //endregion update questions index

        //region update blogs index
        $elasticsearchLibrary->update_by_query(
            $elasticsearchLibrary->get_term('id_user', $userId),
            "ctx._source['fname']='{$fname}';ctx._source['lname']='{$lname}'",
            'blogs'
        );
        //endregion update blogs index

        //region update company index
        $elasticsearchLibrary->update_by_query(
            $elasticsearchLibrary->get_term('id_user', $userId),
            "ctx._source['user_name']='{$fname} {$lname}'",
            'company'
        );
        //endregion update company index
    }
}
