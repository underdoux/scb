<?php
requireAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Generate Social Content</title>
    <link rel="stylesheet" href="/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a>
            <a href="/about">About</a>
            <a href="/dashboard">Dashboard</a>
            <a href="/logout" class="logout-btn">Logout</a>
        </nav>
    </header>
    
    <!-- Hero Section -->
    <section class="hero" style="background-image: url('https://images.pexels.com/photos/3771069/pexels-photo-3771069.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260');">
        <div class="overlay">
            <h1>Social Content Generator</h1>
            <p>Generate engaging social media content with one click.</p>
        </div>
    </section>

    <!-- Main Content Generation Form -->
    <main>
        <div class="container">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="generator-card">
                <h2>Generate New Content</h2>
                <form id="contentGenForm">
                    <div class="form-group">
                        <label for="topic">Content Topic<span class="required">*</span>:</label>
                        <input type="text" id="topic" name="topic" placeholder="Enter a topic (e.g., travel tips, tech trends)" required />
                    </div>

                    <div class="form-group">
                        <label for="contentType">Content Type:</label>
                        <select id="contentType" name="contentType">
                            <option value="promotional">Promotional</option>
                            <option value="informative">Informative</option>
                            <option value="engaging">Engaging</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="length">Content Length:</label>
                        <select id="length" name="length">
                            <option value="short">Short (< 100 characters)</option>
                            <option value="medium" selected>Medium (100-200 characters)</option>
                            <option value="long">Long (200-280 characters)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tone">Tone:</label>
                        <select id="tone" name="tone">
                            <option value="professional">Professional</option>
                            <option value="casual">Casual</option>
                            <option value="humorous">Humorous</option>
                        </select>
                    </div>

                    <button type="submit" class="button primary"><i class="fas fa-magic"></i> Generate Content</button>
                </form>

                <div class="rate-limit-info">
                    <p>Remaining requests: <span id="remainingRequests">10</span> per minute</p>
                </div>

                <div id="loading" style="display:none;">
                    <i class="fas fa-spinner fa-spin"></i> Generating, please wait...
                </div>

                <div id="result" class="content-result"></div>
            </div>
        </div>
    </main>

    <footer>
        <p>© 2023 My PHP Project</p>
    </footer>

    <!-- Inline JavaScript for Content Generation -->
    <script>
        document.getElementById('contentGenForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const topic = document.getElementById('topic').value.trim();
            const resultContainer = document.getElementById('result');
            const loadingIndicator = document.getElementById('loading');
            
            resultContainer.innerHTML = '';
            
            if (!topic) {
                alert('Please enter a content topic.');
                return;
            }
            
            loadingIndicator.style.display = 'block';
            
            const contentType = document.getElementById('contentType').value;
            const length = document.getElementById('length').value;
            const tone = document.getElementById('tone').value;

            fetch('/api/generate-content', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    topic: topic,
                    contentType: contentType,
                    length: length,
                    tone: tone
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                loadingIndicator.style.display = 'none';
                if(data.error) {
                    resultContainer.innerHTML = `<div class="alert alert-error">${data.error}</div>`;
                } else {
                    // Update remaining requests
                    document.getElementById('remainingRequests').textContent = data.remaining_requests;

                    // Format and display the generated content
                    resultContainer.innerHTML = `
                        <div class="content-section">
                            <h3>Generated Caption</h3>
                            <p class="caption">${data.content.caption}</p>
                            
                            <h3>Hashtags</h3>
                            <div class="hashtags">
                                ${data.content.hashtags.map(tag => `<span class="hashtag">${tag}</span>`).join(' ')}
                            </div>
                            
                            <h3>Alternative Caption Variations</h3>
                            <div class="variations">
                                ${data.content.variations.map(alt => `
                                    <div class="variation">
                                        <p>${alt}</p>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
                resultContainer.innerHTML = `<div class="alert alert-error">${error.message}</div>`;
            });
        });
    </script>
</body>
</html>
