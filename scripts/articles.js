async function loadMorePosts() {
    const posts = document.querySelectorAll('li.post');
    var totalPostsLoaded = posts.length;

    const postsToLoad = 10;

    const formData = new FormData();
    formData.append('start_index', totalPostsLoaded);
    formData.append('end_index', totalPostsLoaded + postsToLoad);

    formData.append('sort_type', 'date-edited');
    formData.append('sort_order', 'descending');

    formData.append('only_from_user', false);

    const response = await fetch('./api/post_pagination.php', {
        method: 'POST',
        body: formData
    });

    const postList = posts[0].parentElement;

    if (response.ok) {
        const data = await response.json();
        const newPosts = data['posts'];

        newPosts.forEach((post) => {
            postList.innerHTML += `
                <li class="post">
                <a href="pages/article.php?uuid=${post.uuid}">
                    ${post.title}
                </a>
            </li>
            `;
        });

        if (data['more_exists'] === false) {
            const loadMoreButton = document.getElementById("load-more-posts");
            loadMoreButton.remove();
        }
    }
}