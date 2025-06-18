// scripts/articles.js
async function loadMorePosts() {
    const list = document.querySelector('.article-list');
    if (!list)
        return;

    const articles = list.querySelectorAll('li');
    const articlesLoaded = articles.length;
    const articlesToLoad = 10;

    const formData = new FormData();
    formData.append('start_index', articlesLoaded);
    formData.append('end_index', articlesLoaded + articlesToLoad);
    formData.append('sort_type', 'date-edited');
    formData.append('sort_order', 'descending');
    formData.append('only_from_user', false);

    const result = await fetch('./api/post_pagination.php', {
        method: 'POST',
        body: formData
    });

    if (!result.ok) {
        console.error('Failed to load posts');
        return;
    }

    const data = await result.json();
    const newPosts = data.posts;

    newPosts.forEach(p => {
        const li = document.createElement('li');

        li.innerHTML =
            `<a class="article-hook" href="pages/article.php?uuid=${p.uuid}">
                <img class="article-thumb" src="articles/${p.uuid}/cover.webp" alt="${p.title}">
                <div class="article-body">
                    <h3>${p.title}</h3>
                    <p>${p.summary}</p>
                </div>
            </a>`;

        list.appendChild(li);
    });

    if (data.more_exists === false) {
        const button = document.getElementById('load-more-posts');
        if (button) 
            button.remove();
    }
}
