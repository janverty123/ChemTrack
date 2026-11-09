from pathlib import Path
from math import cos, sin, pi, hypot
from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parent
S = 2
C = {'page':'#F7FAF8','white':'#FFFFFF','ink':'#173C35','teal':'#115E59',
     'mint':'#E6F2EC','muted':'#526760','line':'#D7E4DD','control':'#7A9087'}
im = Image.new('RGB', (1540*S, 1130*S), C['page'])
d = ImageDraw.Draw(im)


def face(size, weight=400, family='inter'):
    result = ImageFont.truetype(str(ROOT/'fonts'/f'{family}-variable.ttf'), round(size*S))
    result.set_variation_by_axes([weight] if family=='manrope' else [min(32,max(14,size)),weight])
    return result


def txt(x,y,label,size=16,weight=400,family='inter',color='ink',anchor='lt'):
    d.text((x*S,y*S),label,font=face(size,weight,family),fill=C.get(color,color),anchor=anchor)


def box(coords,color='white',radius=16,outline='line'):
    d.rounded_rectangle(tuple(int(v*S) for v in coords),radius=int(radius*S),fill=C[color],outline=C[outline] if outline else None,width=S)


def stroke(points,color='line',width=1):
    d.line([(round(x*S),round(y*S)) for x,y in points],fill=C[color],width=round(width*S),joint='curve')


def circle(cx,cy,r,percent,width=12):
    bounds=((cx-r)*S,(cy-r)*S,(cx+r)*S,(cy+r)*S)
    d.ellipse(bounds,outline=C['mint'],width=width*S)
    if percent>0:
        d.arc(bounds,-90,-90+percent*3.6,fill=C['teal'],width=width*S)


def oval_progress(bounds,percent):
    x1,y1,x2,y2=bounds
    r=(y2-y1)/2
    left=x1+r
    right=x2-r
    cy=(y1+y2)/2
    mid=(x1+x2)/2
    points=[(mid,y1),(right,y1)]
    points += [(right+r*cos(-pi/2+i*pi/100),cy+r*sin(-pi/2+i*pi/100)) for i in range(101)]
    points += [(left,y2)]
    points += [(left+r*cos(pi/2+i*pi/100),cy+r*sin(pi/2+i*pi/100)) for i in range(101)]
    points += [(mid,y1)]
    total=sum(hypot(b[0]-a[0],b[1]-a[1]) for a,b in zip(points,points[1:]))
    target=total*percent/100
    output=[points[0]]
    travelled=0
    for a,b in zip(points,points[1:]):
        distance=hypot(b[0]-a[0],b[1]-a[1])
        if travelled+distance<=target:
            output.append(b)
            travelled+=distance
        else:
            fraction=(target-travelled)/distance if distance else 0
            output.append((a[0]+fraction*(b[0]-a[0]),a[1]+fraction*(b[1]-a[1])))
            break
    stroke(points,'mint',7)
    stroke(output,'teal',7)


assert 2/10*100==20
assert 2/5*100==40
txt(44,36,'Dashboard and module progress',32,700,'manrope')
txt(46,85,'Static concept · fictional data · Google Forms return flow deferred',15,color='muted')

box((40,130,940,1065),radius=22)
txt(76,164,'Your progress',30,700,'manrope')
box((783,159,902,199),'mint',20,None)
txt(842,172,'ST2-G',15,600,color='teal',anchor='mt')

circle(306,360,146,20,14)
txt(306,292,'20%',64,700,'manrope',anchor='mt')
txt(306,374,'Overall completed',16,500,color='muted',anchor='mt')
txt(306,402,'2 of 10 required activities',13,color='muted',anchor='mt')
box((214,431,398,471),'page',20)
box((217,434,307,468),'teal',17,None)
txt(262,445,'Cycle 1',13,600,color='white',anchor='mt')
txt(353,445,'Cycle 2',13,500,color='muted',anchor='mt')

txt(562,250,'Cycle 1 selected',16,600,color='teal')
txt(562,287,'2 of 5 tasks complete',22,600,'manrope')
txt(562,334,'Next: Module 2',18,600)
txt(562,369,'4 of 10 pages viewed',15,color='muted')
txt(562,399,'Reading and completion are separate.',14,color='muted')
box((562,441,743,491),'teal',12,None)
txt(594,458,'Resume module',15,600,color='white')

stroke([(76,541),(902,541)])
txt(76,568,'Cycle 1 activities',23,700,'manrope')
txt(705,575,'Switch cycles above',13,color='muted')
tasks=[
    ('Pre-test','Completed',100),
    ('Module 1','Completed',100),
    ('Module 2','40% viewed · in progress',40),
    ('Video lesson','Locked',0),
    ('Post-test / Summative','Locked',0),
]
for index,(label,status,percent) in enumerate(tasks):
    cy=651+index*84
    if index==2:
        box((65,cy-37,915,cy+37),'page',12,None)
    circle(108,cy,25,percent,4)
    txt(108,cy-7,f'{percent}%',11,600,color='teal' if percent else 'muted',anchor='mt')
    txt(158,cy-20,label,19,600,'manrope')
    txt(158,cy+10,status,13,color='muted')
    txt(850,cy-7,'Review' if percent==100 else ('Continue' if index==2 else 'Locked'),13,600,color='teal' if percent else 'muted',anchor='mt')

box((978,130,1500,1065),radius=22)
txt(1010,165,'Module 2',15,600,color='teal')
txt(1010,198,'Bonding basics',27,700,'manrope')
txt(1382,171,'PDF reader',13,color='muted')
box((1007,250,1466,903),'page',8)
box((1023,268,1444,878),'white',2)
txt(1055,302,'Sample document · Page 4',12,color='muted')
txt(1055,341,'Chemical bonds',27,600,'manrope')
txt(1055,393,'A teacher-provided PDF appears here.',15,color='muted')
txt(1055,420,'The page keeps its original formatting.',15,color='muted')
for y,length in [(475,337),(494,310),(513,327),(532,280)]:
    stroke([(1055,y),(1055+length,y)],'line',3)
stroke([(1158,624),(1305,624)],'teal',3)
for x,label in [(1158,'A'),(1305,'B')]:
    d.ellipse(((x-34)*S,590*S,(x+34)*S,658*S),fill=C['mint'],outline=C['teal'],width=2*S)
    txt(x,610,label,24,600,'manrope',anchor='mt')
txt(1055,714,'Concepts',19,600,'manrope')
for y,length in [(758,330),(779,315),(800,335)]:
    stroke([(1055,y),(1055+length,y)],'line',3)
stroke([(1456,267),(1456,882)],'line',3)
stroke([(1456,464),(1456,558)],'control',4)

box((1008,925,1470,1013),'white',44,None)
oval_progress((1011,928,1467,1010),40)
txt(1239,945,'Stage 2 of 4 · Concepts',16,600,anchor='mt')
txt(1239,975,'Page 4 of 10 · 40% viewed',13,color='muted',anchor='mt')
txt(1239,1034,'Bottom-centre oval tracker · saved position',12,color='muted',anchor='mt')
txt(44,1094,'Proposal only. The large circle counts completed requirements; module circles show page-viewing progress.',14,color='muted')

im.resize((1540,1130),Image.Resampling.LANCZOS).save(ROOT/'dashboard-reader-progress-preview.png')
print('Created single-overall-circle dashboard and oval reader tracker preview. Fixture: 2/10 complete; 4/10 pages viewed.')
