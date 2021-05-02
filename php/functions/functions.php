<?php

function selectOneQuery($sql): ?array {
    require('connection.php');
    $response = $con->query($sql);
    $result = (!empty($response)) ? $response->fetch_assoc() : null;
    $con->close();
    return $result;
}

function selectAllQuery($sql): ?array {
    require('connection.php');
    $response = $con->query($sql);
    $result = (!empty($response)) ? $response->fetch_all(MYSQLI_ASSOC) : null;
    $con->close();
    return $result;
}
function gettitlesformainrecent()
{
    $sql = "SELECT title_id, title_name, title_description, title_cover from titles ORDER BY title_id DESC LIMIT 8";
    return selectAllQuery($sql);
}
function gettitlesformainpopular()
{
    $sql = "SELECT title_id, title_name, title_description, title_cover from titles ORDER BY visit_counter DESC LIMIT 8";
    return selectAllQuery($sql);
}
function gettitleinfo($id)
{
    $sql = "SELECT * from titles where title_id = ".$id." ";
    return selectOneQuery($sql);
}
function visitcounter($id)
{
    require('connection.php');
    $sql = "UPDATE titles SET visit_counter = visit_counter + 1 where title_id = ".$id." ";
    $res = $con->query($sql);
}

function getchapterlist($id)
{
    $sql = "SELECT chapter_id, chapter_name from chapters where title_id = " . $id . " ";
    return selectAllQuery($sql);
}

function getchapter($tid,$cid){
    $sql = "SELECT * from chapters where title_id =".$tid." and chapter_id = ".$cid." ";
    return selectOneQuery($sql);
}
function chapterchecknext($tid,$cid){
    $sql = "SELECT chapter_id from chapters where title_id =".$tid." and chapter_id = ".$cid + 1 ." ";
    return (!empty(selectOneQuery($sql)));
}
function chaptercheckprevious($tid,$cid){
    $sql = "SELECT chapter_id from chapters where title_id =".$tid." and chapter_id = ".$cid - 1 ." ";
    return (!empty(selectOneQuery($sql)));
}
function search(){
    $search= $_POST['search'];
    $sql = "SELECT title_id, title_name, title_description, title_cover from titles where title_name like '%$search%' ORDER BY title_id DESC";
    return selectAllQuery($sql);
}
// views
function carousel(string $name, array $data = null):void
{

    if (empty($data)) {
        echo "Кажется что-то пошло не так при загрузке \"{$name}\".";
        return;
    }
    $cards = null;
    foreach ($data as $poster) {
        $cards .= <<<HTML
        <div class="poster-card">
            <div class="poster-img">
                <img src="{$poster['title_cover']}" alt="{$poster['title_name']} poster">
            </div>
            <div class="poster-annotation">
                <div class="container">
                    <div class="center"><b>{$poster['title_name']}</b></div>
                    <div><p>{$poster['title_description']}</p></div>
                </div>
            </div>
            <a href="/read/{$poster['title_id']}"></a>
            <!--<a href="/read/{$poster['title_id']}"></a>-->
            <!--<a href="/read?title={$poster['title_id']}"></a>-->
        </div>
HTML;

    }

    $carousel = <<<HTML
    <div class="card p-1">
        <h1>$name</h1>
        <div class="horizontal-scrollable">
        $cards
        </div>
    </div>
HTML;
    echo $carousel;
}


function generateTitlePage(int $title_id): void {
    $titleInfo = gettitleinfo($title_id);
    if (empty($titleInfo)) {
        print_r("Nothing found. 404!");
        exit(0);
    }
    $chaptersList = getchapterlist($title_id);
    visitcounter($title_id);

    $chapters = null;
    foreach ($chaptersList as $chapter) {
        $chapters .= <<< HTML
        <li><a href="/read/{$titleInfo['title_id']}/{$chapter['chapter_id']}">{$chapter['chapter_name']}</a></li>
HTML;
    }

    $page = <<<HTML
    <div class="container card p-1">
            <div class="flex">
                <div class="title-img">
                    <img src="{$titleInfo['title_cover']}" alt="{$titleInfo['title_jpname']} poster">
                </div>
                <div class="title-info">
                    <div class="title-name">
                        <h1>{$titleInfo['title_name']}</h1>
                    </div>
                    <div class="title-status muted"><p>{$titleInfo['status_translate']}; {$titleInfo['status_publish']}</p></div>
                    <div class="grid">
                        <div>Жанры:</div>
                        <div>{$titleInfo['genres']}</div>
                        <div>Автор:</div>
                        <div>{$titleInfo['author']}</div>
                        <div>Альт. имя:</div>
                        <div>{$titleInfo['title_jpname']}</div>
                        <div>годы выпуска:</div>
                        <div>{$titleInfo['years']}</div>
                        <div>Эту страницы посетили</div>
                        <div>{$titleInfo['visit_counter']} раз(-а)</div>
                    </div>
                </div>
            </div>
            <div>
                <h2>Аннотация</h2>
                <p>{$titleInfo['title_description']}</p>
            </div>
            <div>
                <h2>Chapters</h2>
                <div class="vertical-scrollable card">
                    <ol class="chapters-list">
                        {$chapters}
                    </ol>
                </div>
            </div>
        </div>
HTML;

    echo $page;
}
?>
