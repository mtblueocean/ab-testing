<div id="zva-modal-area">
    <div id="zva-modal-overlay">
    </div>
    <div id="zva-modal">
        <h2>PRE-ORDER THE GAME DEVELOPMENT MINI-DEGREE</h2>
        <div>
            <p><strong>ON KICKSTARTER!</strong> The <strong>Game Development Mini-Degree</strong> is the world's most effective way to learn to code and build YOUR OWN games with Unity. Learn to create 2D, 3D, Mobile, Virtual Reality and Augmented Reality games from total scratch!</p>
            
            <div style="text-align: center;margin-bottom: 40px;">
                <a href="<?php echo get_option('zva_modal_url') ?>"><img width="380" src="https://gamedevacademy.org/wp-content/uploads/2017/08/unity-md-play-btn.png" /></a>
<!--                <iframe width="374" height="210" src="https://www.youtube.com/embed/5hLCUU8NowA" frameborder="0" allowfullscreen></iframe>-->
            </div>
<!--            <p>Check out the <strong>heavily discounted reward packages</strong> which include access to our most popular online courses 
                such as <strong>The Complete Mobile Game Development Course</strong> and our web development titles.</p>
        --></div>
        <div style="text-align: left;">
            <a id="zva-modal-go"  style="background:#009D84;" href="<?php echo get_option('zva_modal_url') ?>">ENROLL</a>
            <button id="zva-modal-close">Maybe later</button>
        </div>
    </div>
</div>
<script>
    window.addEventListener("load",function() {
        
        setTimeout(function() {
            if(document.body.clientWidth > 780) {
                document.getElementById('zva-modal-area').style.top = '0px';
                document.getElementById('zva-modal-area').style.height = document.body.clientHeight + 'px';
                document.getElementById('zva-modal-area').style.display = 'block';
                document.getElementById('zva-modal').style.top = (document.documentElement.scrollTop + 110 ) + 'px';

                document.getElementById('zva-modal-overlay').addEventListener('click', function(e){
                    document.getElementById('zva-modal-area').style.display = 'none';
                });
                document.getElementById('zva-modal-close').addEventListener('click', function(e){
                    document.getElementById('zva-modal-area').style.display = 'none';
                });
            }
        }, 5000);
    });
</script>