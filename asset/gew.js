var Gew = {};

(function (Gew) {
    Gew.elements = null;
    Gew.simplified = false;

    var setupStructure = function (gew, isRequired) {
        var emotions = Gew.emotions;

        emotions.forEach(function (emotion, index) {
            gew.appendChild(getEmotionStructure(gew, index, emotion, isRequired));

        });

        var neutralStructure = getNeutralStructure(gew, isRequired);

        gew.appendChild(neutralStructure);
    }

    var getEmotionStructure = function (gew, index, emotion, isRequired) {
        var quadrant = Math.floor(index / 5);

        var containerId = gew.id + "_emotion_" + index;

        var container = document.createElement("div");
        container.id = containerId;
        container.classList.add("gew-emotion");

        // Five levels of intensity
        for (var i = 0; i < 5; i++) {
            var radio = document.createElement("input");
            radio.id = containerId + "_" + i;
            radio.name = gew.dataset.input;
            radio.type = "radio";
            radio.value = Gew.simplified ? quadrant : Gew.text(emotion) + ";" + (i + 1);
            radio.required = isRequired;

            container.appendChild(radio);

        }

        // Five levels of intensity
        for (var i = 0; i < 5; i++) {
            var label = document.createElement("label");
            label.htmlFor = containerId + "_" + i;
            label.title = Gew.text(emotion) + " - " + (i + 1);

            container.appendChild(label);
        }

        var description = document.createElement("p");
        description.textContent = Gew.text(emotion);

        container.appendChild(description);

        return container;
    }

    var getNeutralStructure = function (gew, isRequired) {
        var neutrals = Gew.neutrals;

        var containerId = gew.id + "_neutral";

        var container = document.createElement("div");
        container.id = containerId;
        container.classList.add("gew-neutral");

        neutrals.forEach(function (neutral, i) {
            var radioId = containerId + "_" + i;

            var label = document.createElement("label");
            label.htmlFor = radioId;
            label.title = Gew.text(neutral);
            label.textContent = Gew.text(neutral);

            var radio = document.createElement("input");
            radio.id = radioId;
            radio.name = gew.dataset.input;
            radio.type = "radio";
            radio.value = Gew.simplified ? Gew.code[neutral] : Gew.text(neutral);
            radio.required = isRequired;

            container.appendChild(radio);
            container.appendChild(label);
        });

        return container;
    }

    var setupStyles = function (gew) {
        const CONTAINER_INITIAL_ANGLE = 9;
        const INITIAL_HUE = 120;

        var containers = document.querySelectorAll("#" + gew.id + " div.gew-emotion");
        var angle = CONTAINER_INITIAL_ANGLE;
        var emotions = Gew.emotions;
        var ratio = 360 / emotions.length;

        containers.forEach(function (container, index) {
            styleEmotionIntesities(document.querySelectorAll("#" + container.id + " label"), container.offsetHeight, INITIAL_HUE - ratio * index, 0.10);
            styleEmotionDescription(document.querySelector("#" + container.id + " p"), Math.cos((angle * Math.PI) / 180.0) < 0);

            container.style.width = container.offsetWidth + "px";

            var transformOrigin = "0 0";
            container.style.transformOrigin = transformOrigin;
            container.style.webkitTransformOrigin = transformOrigin;
            container.style.mozTransformOrigin = transformOrigin;
            container.style.msTransformOrigin = transformOrigin;
            container.style.oTransformOrigin = transformOrigin;

            var transform = "rotate(-" + angle + "deg) translate(50%, -50%)";
            container.style.transform = transform;
            container.style.webkitTransform = transform;
            container.style.mozTransform = transform;
            container.style.msTransform = transform;
            container.style.oTransform = transform;

            angle += ratio;

        });

    }

    var styleEmotionIntesities = function (intensities, initialSize, hue, proportion) {
        var percent = 1;
        var color = "hsl(" + hue + ", 100%, 35%)";

        percent = proportion * 5;

        for (var i = 0; i < 5; i++) {
            percent = percent + proportion;
            var size = Math.round(initialSize * percent);

            intensities[i].style.width = size + "px";
            intensities[i].style.height = size + "px";
            intensities[i].style.opacity = percent;
            intensities[i].style.borderColor = color;
        }
    }

    var styleEmotionDescription = function (description, isNegativeAngle) {
        var transform = "rotate(-4deg)";

        if (isNegativeAngle) {
            transform += "scale(-1, -1) translate(10%, -30%)";
        } else
        {
            transform += "translate(-10%, 30%)";
        }

        description.style.transform = transform;
        description.style.webkitTransform = transform;
        description.style.mozTransform = transform;
        description.style.msTransform = transform;
        description.style.oTransform = transform;

        var transformOrigin = "50% 50%";
        description.style.transformOrigin = transformOrigin;
        description.style.webkitTransformOrigin = transformOrigin;
        description.style.mozTransformOrigin = transformOrigin;
        description.style.msTransformOrigin = transformOrigin;
        description.style.oTransformOrigin = transformOrigin;
    }

    var setupHandlers = function (gew) {
        var intensities = document.querySelectorAll("#" + gew.id + " div.gew-emotion label");

        var action = function (element) {
            var selected = document.querySelector("#" + gew.id + " label.gew-selected");
            if (selected != null) {
                selected.classList.remove("gew-selected");
                selected.style.backgroundColor = "";
            }

            element.classList.toggle("gew-selected");
        }

        intensities.forEach(function (intensity) {
            intensity.addEventListener("click", function () {
                action(this);

                this.style.backgroundColor = this.style.borderColor;

            }, false);
        });

        var neutrals = document.querySelectorAll("#" + gew.id + " div.gew-neutral label");

        neutrals[0].addEventListener("click", function () {
            action(this);

        }, false);

        neutrals[1].addEventListener("click", function () {
            action(this);

            this.previousSibling.value = this.title + ";" + prompt(Gew.text("Which emotion?"));

            if( Gew.simplified ) {
                this.previousSibling.value = Gew.code["Other"];
            }

        }, false);

    }

    var setup = function () {
        Gew.elements = document.querySelectorAll(".gew");

        Gew.elements.forEach(function (gew) {
            setupStructure(gew, gew.dataset.required != undefined);
            setupStyles(gew);
            setupHandlers(gew);
        });

    }

    Gew.emotions = [
        "Enjoyment/Pleasure",
        "Happiness/Joy",
        "Pride/Elation",
        "Amusement/Laughter",
        "Involvemment/Interest",
        "Irritation/Anger",
        "Contempt/Scorn",
        "Disgust/Repulsion",
        "Envy/Jealousy",
        "Disappointment/Regret",
        "Guilt/Remorse",
        "Embarrassment/Shame",
        "Worry/Fear",
        "Sadness/Despair",
        "Pity/Compassion",
        "Longing/Nostalgia",
        "Astonishment/Surprise",
        "Feeling disburdened/Relief",
        "Wonderment/Felling awe",
        "Tenderness/Felling love"
    ];

    Gew.neutrals = [
        "None",
        "Other"
    ];

    Gew.code = {
        "None": 5,
        "Other": 4
    }

    Gew.lang = document.documentElement.lang;

    Gew.init = function (simplified = false) {
        Gew.simplified = simplified
        setup();
    }

    Gew.text = function (message) {
        var messages = {
            "pt-br": {
                "Enjoyment/Pleasure": "Aproveitar/Prazer",
                "Happiness/Joy": "Felicidade/Alegria",
                "Pride/Elation": "Orgulhoso/Eufórico",
                "Amusement/Laughter": "Entusiasmado/Divertido",
                "Involvemment/Interest": "Interessado/Envolvido",
                "Irritation/Anger": "Irritado/Raiva",
                "Contempt/Scorn": "Desrespeito/Desprezo",
                "Disgust/Repulsion": "Nojo/Repulsão",
                "Envy/Jealousy": "Inveja/Ciúme",
                "Disappointment/Regret": "Decepcionado/Arrependimento",
                "Guilt/Remorse": "Culpa/Remorso",
                "Embarrassment/Shame": "Embaraço/Vergonha",
                "Worry/Fear": "Procupação/Medo",
                "Sadness/Despair": "Tristeza/Desespero",
                "Pity/Compassion": "Pena/Compaixão",
                "Longing/Nostalgia": "Saudade/Nostalgia",
                "Astonishment/Surprise": "Espantado/Surpreso",
                "Feeling disburdened/Relief": "Despreocupado/Aliviado",
                "Wonderment/Felling awe": "Assustado/Intimidado",
                "Tenderness/Felling love": "Ternura/Amável",
                "None": "Nenhuma",
                "Other": "Outra",
                "Which emotion?": "Qual emoção?"
            }

        };

        if (messages[Gew.lang] != undefined) {
            return messages[Gew.lang][message];
        }

        return message;
    };

})(Gew);