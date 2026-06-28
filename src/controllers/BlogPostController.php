<?php
include_once __DIR__ . '/../models/BlogPost.php';
include_once __DIR__ . '/../config/auth.php';

class BlogPostController {
    private $db;
    private $blogPost;

    public function __construct($db) {
        $this->db = $db;
        $this->blogPost = new BlogPost($db);
    }

    public function getAll($params = []) {
        return $this->blogPost->readPaginated($params);
    }

    public function getOne($id) {
        $this->blogPost->id = $id;
        $stmt = $this->blogPost->readOne();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) return json_encode($row);
        http_response_code(404);
        return json_encode(array("message" => "Not found."));
    }

    public function getBySlug($slug) {
        $stmt = $this->blogPost->readBySlug($slug);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) return json_encode($row);
        http_response_code(404);
        return json_encode(array("message" => "Not found."));
    }

    public function create($data) {
        if(!empty($data->title) && !empty($data->slug)) {
            $this->blogPost->title = $data->title;
            $this->blogPost->slug = $data->slug;
            $this->blogPost->excerpt = $data->excerpt ?? '';
            $this->blogPost->content = $data->content ?? '';
            $this->blogPost->author = $data->author ?? '';
            $this->blogPost->category = $data->category ?? '';
            $this->blogPost->cover_image = $data->cover_image ?? '';
            $this->blogPost->status = $data->status ?? 'draft';
            $this->blogPost->published_at = $data->published_at ?? null;

            if($this->blogPost->create()) {
                http_response_code(201);
                return json_encode(array("message" => "Blog post created."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to create blog post."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Incomplete data. Title and slug are required."));
        }
    }

    public function update($data) {
        if(!empty($data->id)) {
            $this->blogPost->id = $data->id;
            $this->blogPost->title = $data->title ?? '';
            $this->blogPost->slug = $data->slug ?? '';
            $this->blogPost->excerpt = $data->excerpt ?? '';
            $this->blogPost->content = $data->content ?? '';
            $this->blogPost->author = $data->author ?? '';
            $this->blogPost->category = $data->category ?? '';
            $this->blogPost->cover_image = $data->cover_image ?? '';
            $this->blogPost->status = $data->status ?? 'draft';
            $this->blogPost->published_at = $data->published_at ?? null;

            if($this->blogPost->update()) {
                return json_encode(array("message" => "Blog post updated."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to update blog post."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "ID is required."));
        }
    }

    public function delete($id) {
        $this->blogPost->id = $id;
        if($this->blogPost->delete()) {
            return json_encode(array("message" => "Blog post deleted."));
        } else {
            http_response_code(503);
            return json_encode(array("message" => "Unable to delete blog post."));
        }
    }
}
